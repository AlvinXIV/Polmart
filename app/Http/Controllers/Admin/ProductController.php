<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMutation;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::query();

        if ($search = $request->string('search')->trim()->value()) {
            $query->where(function ($q) use ($search) {
                $q->where('sku', 'ilike', "%{$search}%")
                    ->orWhere('name', 'ilike', "%{$search}%")
                    ->orWhere('category', 'ilike', "%{$search}%");
            });
        }

        $products = $query->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.products.index', compact('products'));
    }

    public function create(): View
    {
        return view('admin.products.create');
    }

    public function store(Request $request, StockService $stockService): RedirectResponse
    {
        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:50', 'unique:products,sku'],
            'name' => ['required', 'string', 'max:150'],
            'category' => ['required', 'string', 'max:80'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        DB::transaction(function () use ($request, $validated, $stockService) {
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('products', 'public');
            }

            $initialStock = (int) $validated['stock'];

            $product = Product::create([
                'sku' => strtoupper(trim($validated['sku'])),
                'name' => trim($validated['name']),
                'category' => trim($validated['category']),
                'price' => $validated['price'],
                'stock' => 0,
                'image' => $imagePath,
                'is_active' => true,
            ]);

            if ($initialStock > 0) {
                $stockService->recordMutation(
                    product: $product,
                    type: 'in',
                    qty: $initialStock,
                    userId: $request->user()->id,
                    reference: 'INIT-' . $product->sku,
                    notes: 'Stok awal saat pendaftaran master barang'
                );
            }
        });

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Master barang berhasil ditambahkan.');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.edit', compact('product'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:50', Rule::unique('products', 'sku')->ignore($product->id)],
            'name' => ['required', 'string', 'max:150'],
            'category' => ['required', 'string', 'max:80'],
            'price' => ['required', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $product->image = $request->file('image')->store('products', 'public');
        }

        $product->sku = strtoupper(trim($validated['sku']));
        $product->name = trim($validated['name']);
        $product->category = trim($validated['category']);
        $product->price = $validated['price'];
        $product->is_active = $request->boolean('is_active');
        $product->save();

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Data barang berhasil diperbarui.');
    }

    public function toggleStatus(Product $product): RedirectResponse
    {
        $product->update([
            'is_active' => ! $product->is_active,
        ]);

        $label = $product->is_active ? 'diaktifkan kembali' : 'dinonaktifkan';

        return redirect()
            ->route('admin.products.index')
            ->with('success', "Barang {$product->name} berhasil {$label}.");
    }

    public function restock(Request $request, Product $product, StockService $stockService): RedirectResponse
    {
        $validated = $request->validate([
            'qty' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $stockService->recordMutation(
            product: $product,
            type: 'in',
            qty: (int) $validated['qty'],
            userId: $request->user()->id,
            reference: 'RESTOCK-' . now()->format('YmdHis'),
            notes: $validated['notes'] ?: 'Penambahan stok masuk manual oleh admin'
        );

        return redirect()
            ->route('admin.products.index')
            ->with('success', "Stok {$product->name} berhasil ditambah sebanyak {$validated['qty']}.");
    }

    public function mutations(): View
    {
        $mutations = StockMutation::with(['product', 'user'])
            ->latest()
            ->paginate(20);

        return view('admin.products.mutations', compact('mutations'));
    }
}