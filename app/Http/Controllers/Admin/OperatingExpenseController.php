<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OperatingExpense;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OperatingExpenseController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        // Validasi parameter filter tanggal
        $validator = Validator::make($request->all(), [
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ], [
            'date_from.date' => 'Format tanggal awal filter tidak valid.',
            'date_to.date' => 'Format tanggal akhir filter tidak valid.',
            'start_date.date' => 'Format tanggal awal filter tidak valid.',
            'end_date.date' => 'Format tanggal akhir filter tidak valid.',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('admin.operating-expenses.index')
                ->withErrors($validator)
                ->withInput();
        }

        $query = OperatingExpense::with('user');

        // Pencarian (Nama, Kode Pengeluaran, Catatan)
        if ($search = $request->string('search')->trim()->value()) {
            $pattern = '%' . mb_strtolower($search) . '%';
            $query->where(function ($q) use ($pattern) {
                $q->whereRaw('LOWER(name) LIKE ?', [$pattern])
                    ->orWhereRaw('LOWER(expense_code) LIKE ?', [$pattern])
                    ->orWhereRaw('LOWER(notes) LIKE ?', [$pattern]);
            });
        }

        // Filter Kategori
        if ($category = $request->string('category')->trim()->value()) {
            if (array_key_exists($category, OperatingExpense::CATEGORIES)) {
                $query->where('category', $category);
            }
        }

        // Filter Rentang Tanggal (hanya tanggal terverifikasi yang masuk ke query)
        $rawDateFrom = $request->input('date_from', $request->input('start_date'));
        $rawDateTo = $request->input('date_to', $request->input('end_date'));

        if ($rawDateFrom) {
            $query->whereDate('expense_date', '>=', Carbon::parse($rawDateFrom)->toDateString());
        }
        if ($rawDateTo) {
            $query->whereDate('expense_date', '<=', Carbon::parse($rawDateTo)->toDateString());
        }

        // Statistik Ringkasan Query Aktif
        $statsQuery = clone $query;
        $totalAmount = (float) $statsQuery->sum('amount');
        $totalCount = (int) $statsQuery->count();
        $avgAmount = $totalCount > 0 ? $totalAmount / $totalCount : 0.0;

        $expenses = $query->orderByDesc('expense_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $categories = OperatingExpense::CATEGORIES;

        return view('admin.operating-expenses.index', compact(
            'expenses',
            'categories',
            'totalAmount',
            'totalCount',
            'avgAmount'
        ));
    }

    public function create(): View
    {
        $categories = OperatingExpense::CATEGORIES;
        $previewCode = OperatingExpense::generateExpenseCode();

        return view('admin.operating-expenses.create', compact('categories', 'previewCode'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'category' => ['required', 'string', Rule::in(array_keys(OperatingExpense::CATEGORIES))],
            'amount' => ['required', 'numeric', 'min:1'],
            'expense_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'receipt' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);

        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            // Disimpan pada disk privat ('local'), bukan public disk
            $receiptPath = $request->file('receipt')->store('operating-expenses', 'local');
        }

        $maxRetries = 3;
        for ($attempt = 0; $attempt < $maxRetries; $attempt++) {
            try {
                DB::transaction(function () use ($validated, $receiptPath, $attempt) {
                    $expenseCode = OperatingExpense::generateExpenseCode($validated['expense_date'], $attempt);

                    OperatingExpense::create([
                        'expense_code' => $expenseCode,
                        'name' => $validated['name'],
                        'category' => $validated['category'],
                        'amount' => $validated['amount'],
                        'expense_date' => $validated['expense_date'],
                        'user_id' => Auth::id(),
                        'notes' => $validated['notes'] ?? null,
                        'receipt_path' => $receiptPath,
                    ]);
                });
                break;
            } catch (QueryException $e) {
                $isUniqueViolation = str_contains($e->getMessage(), 'unique')
                    || str_contains($e->getMessage(), 'UNIQUE')
                    || str_contains($e->getMessage(), '23505')
                    || str_contains($e->getMessage(), '19');

                if ($isUniqueViolation && $attempt < $maxRetries - 1) {
                    continue; // Collision unik terdeteksi, coba lagi dengan offset increment
                }

                // Jika bukan error unik atau max retry tercapai, bersihkan file dan teruskan error asli
                if ($receiptPath && Storage::disk('local')->exists($receiptPath)) {
                    Storage::disk('local')->delete($receiptPath);
                }
                throw $e;
            } catch (\Throwable $e) {
                if ($receiptPath && Storage::disk('local')->exists($receiptPath)) {
                    Storage::disk('local')->delete($receiptPath);
                }
                throw $e;
            }
        }

        return redirect()
            ->route('admin.operating-expenses.index')
            ->with('success', 'Biaya operasional berhasil dicatat.');
    }

    public function edit(OperatingExpense $operatingExpense): View
    {
        $categories = OperatingExpense::CATEGORIES;

        return view('admin.operating-expenses.edit', compact('operatingExpense', 'categories'));
    }

    public function update(Request $request, OperatingExpense $operatingExpense): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'category' => ['required', 'string', Rule::in(array_keys(OperatingExpense::CATEGORIES))],
            'amount' => ['required', 'numeric', 'min:1'],
            'expense_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'delete_receipt' => ['nullable', 'boolean'],
            'receipt' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);

        $shouldDeleteOld = false;
        $newReceiptPath = null;
        $oldReceiptPath = $operatingExpense->receipt_path;

        if ($request->hasFile('receipt')) {
            // Upload baru mendapat prioritas utama dan menggantikan berkas lama
            $newReceiptPath = $request->file('receipt')->store('operating-expenses', 'local');
            $shouldDeleteOld = true;
        } elseif ($request->boolean('delete_receipt')) {
            // Checkbox hapus dipilih tanpa unggah file baru
            $shouldDeleteOld = true;
        }

        try {
            DB::transaction(function () use ($operatingExpense, $validated, $newReceiptPath, $request) {
                $updateData = [
                    'name' => $validated['name'],
                    'category' => $validated['category'],
                    'amount' => $validated['amount'],
                    'expense_date' => $validated['expense_date'],
                    'notes' => $validated['notes'] ?? null,
                ];

                if ($newReceiptPath !== null) {
                    $updateData['receipt_path'] = $newReceiptPath;
                } elseif ($request->boolean('delete_receipt')) {
                    $updateData['receipt_path'] = null;
                }

                $operatingExpense->update($updateData);
            });

            // Hapus berkas lama dari storage privat HANYA setelah transaksi database berhasil
            if ($shouldDeleteOld && $oldReceiptPath && Storage::disk('local')->exists($oldReceiptPath)) {
                Storage::disk('local')->delete($oldReceiptPath);
            }
        } catch (\Throwable $e) {
            // Jika transaksi gagal, hapus berkas baru yang sempat terunggah dan pertahankan berkas lama
            if ($newReceiptPath && Storage::disk('local')->exists($newReceiptPath)) {
                Storage::disk('local')->delete($newReceiptPath);
            }
            throw $e;
        }

        return redirect()
            ->route('admin.operating-expenses.index')
            ->with('success', 'Biaya operasional berhasil diperbarui.');
    }

    public function destroy(OperatingExpense $operatingExpense): RedirectResponse
    {
        $receiptPath = $operatingExpense->receipt_path;

        DB::transaction(function () use ($operatingExpense) {
            $operatingExpense->delete();
        });

        // Hapus berkas bukti dari storage privat hanya setelah penghapusan database berhasil
        if ($receiptPath && Storage::disk('local')->exists($receiptPath)) {
            Storage::disk('local')->delete($receiptPath);
        }

        return redirect()
            ->route('admin.operating-expenses.index')
            ->with('success', 'Biaya operasional berhasil dihapus.');
    }

    /**
     * Tampilkan atau unduh bukti nota melalui storage privat yang aman (hanya admin).
     */
    public function receipt(OperatingExpense $operatingExpense): StreamedResponse
    {
        if (! $operatingExpense->receipt_path || ! Storage::disk('local')->exists($operatingExpense->receipt_path)) {
            abort(404, 'Berkas bukti nota tidak ditemukan.');
        }

        return Storage::disk('local')->response(
            $operatingExpense->receipt_path,
            'bukti-' . $operatingExpense->expense_code . '.' . pathinfo($operatingExpense->receipt_path, PATHINFO_EXTENSION)
        );
    }
}
