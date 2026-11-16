<?php

namespace App\Http\Controllers\Admin;

use App\Exports\OrderImportTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\OrderExcelChunkImport;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class OrderImportController extends Controller
{
    public function index(): View
    {
        $importedOrders = Order::with('items.product')
            ->where('source', 'import_excel')
            ->latest('transaction_date')
            ->paginate(15);

        return view('admin.order-imports.index', [
            'importedOrders' => $importedOrders,
            'preview' => null,
        ]);
    }

    public function downloadTemplate(): BinaryFileResponse
    {
        return Excel::download(
            new OrderImportTemplateExport(),
            'template_impor_pesanan_polmart.xlsx'
        );
    }

    public function preview(Request $request): View
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ]);

        $filename = Str::uuid()->toString() . '.' . $request->file('file')->getClientOriginalExtension();
        $tempPath = $request->file('file')->storeAs('imports', $filename, 'local');

        $importer = new OrderExcelChunkImport(commit: false);
        Excel::import($importer, $tempPath, 'local');

        $importedOrders = Order::with('items.product')
            ->where('source', 'import_excel')
            ->latest('transaction_date')
            ->paginate(15);

        return view('admin.order-imports.index', [
            'importedOrders' => $importedOrders,
            'preview' => [
                'temp_path' => $tempPath,
                'original_name' => $request->file('file')->getClientOriginalName(),
                'total_rows' => $importer->totalRows,
                'valid_count' => $importer->validCount,
                'invalid_count' => $importer->invalidCount,
                'valid_total_amount' => $importer->validTotalAmount,
                'rows' => $importer->previewRows,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'temp_path' => ['required', 'string'],
        ]);

        $tempPath = $validated['temp_path'];

        // Pastikan path berada di dalam direktori imports/ yang sah
        if (! str_starts_with($tempPath, 'imports/') || str_contains($tempPath, '..') || ! Storage::disk('local')->exists($tempPath)) {
            return redirect()
                ->route('admin.order-imports.index')
                ->withErrors(['file' => 'Sesi pratinjau file sudah kedaluwarsa. Silakan unggah ulang file Excel.']);
        }

        $importer = new OrderExcelChunkImport(commit: true);
        Excel::import($importer, $tempPath, 'local');

        Storage::disk('local')->delete($tempPath);

        return redirect()
            ->route('admin.order-imports.index')
            ->with(
                'success',
                "Impor selesai: {$importer->validCount} baris item ({$importer->importedOrdersCount} pesanan) berhasil disimpan tanpa memotong stok fisik."
            );
    }
}