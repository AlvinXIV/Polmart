<?php

namespace App\Http\Controllers\Admin;

use App\Exports\MemberImportTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\MemberExcelChunkImport;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MemberImportController extends Controller
{
    public function index(): View
    {
        $members = User::where('role', 'member')
            ->latest()
            ->paginate(15);

        return view('admin.member-imports.index', [
            'members' => $members,
            'preview' => null,
        ]);
    }

    public function downloadTemplate(): BinaryFileResponse
    {
        return Excel::download(
            new MemberImportTemplateExport(),
            'template_impor_anggota_polmart.xlsx'
        );
    }

    public function preview(Request $request): View
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ]);

        $filename = Str::uuid()->toString() . '.' . $request->file('file')->getClientOriginalExtension();
        $tempPath = $request->file('file')->storeAs('imports', $filename, 'local');

        $importer = new MemberExcelChunkImport(commit: false);
        Excel::import($importer, $tempPath, 'local');

        $members = User::where('role', 'member')
            ->latest()
            ->paginate(15);

        return view('admin.member-imports.index', [
            'members' => $members,
            'preview' => [
                'temp_path' => $tempPath,
                'original_name' => $request->file('file')->getClientOriginalName(),
                'total_rows' => $importer->totalRows,
                'valid_count' => $importer->validCount,
                'invalid_count' => $importer->invalidCount,
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

        if (! str_starts_with($tempPath, 'imports/') || str_contains($tempPath, '..') || ! Storage::disk('local')->exists($tempPath)) {
            return redirect()
                ->route('admin.member-imports.index')
                ->withErrors(['file' => 'Sesi pratinjau file sudah kedaluwarsa. Silakan unggah ulang file Excel.']);
        }

        $importer = new MemberExcelChunkImport(commit: true);
        Excel::import($importer, $tempPath, 'local');

        Storage::disk('local')->delete($tempPath);

        return redirect()
            ->route('admin.member-imports.index')
            ->with(
                'success',
                "Impor selesai: {$importer->importedMembersCount} anggota baru berhasil ditambahkan ke sistem."
            );
    }
}
