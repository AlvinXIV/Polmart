<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(): View
    {
        $cartData = $this->buildCartSummary();

        return view('anggota.keranjang', $cartData);
    }

    public function add(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'qty' => ['nullable', 'integer', 'min:1'],
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $qtyToAdd = (int) ($validated['qty'] ?? 1);

        if (! $product->is_active || $product->stock < 1) {
            return back()->withErrors([
                'cart' => "Barang '{$product->name}' sedang tidak tersedia.",
            ]);
        }

        $cart = session('polmart_cart', []);
        $currentQty = (int) ($cart[$product->id] ?? 0);
        $newQty = $currentQty + $qtyToAdd;

        if ($newQty > $product->stock) {
            return back()->withErrors([
                'cart' => "Stok '{$product->name}' tersisa {$product->stock} unit.",
            ]);
        }

        $cart[$product->id] = $newQty;
        session(['polmart_cart' => $cart]);

        return back()->with('success', "'{$product->name}' ({$qtyToAdd}x) ditambahkan ke keranjang.");
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'qty' => ['required', 'integer', 'min:0'],
        ]);

        $qty = (int) $validated['qty'];
        $cart = session('polmart_cart', []);

        if ($qty <= 0) {
            unset($cart[$product->id]);
            session(['polmart_cart' => $cart]);

            return redirect()
                ->route('anggota.keranjang')
                ->with('success', "'{$product->name}' dihapus dari keranjang.");
        }

        if (! $product->is_active || $product->stock < $qty) {
            return redirect()
                ->route('anggota.keranjang')
                ->withErrors(['cart' => "Stok '{$product->name}' tidak mencukupi (tersisa {$product->stock} unit)."]);
        }

        $cart[$product->id] = $qty;
        session(['polmart_cart' => $cart]);

        return redirect()
            ->route('anggota.keranjang')
            ->with('success', "Jumlah '{$product->name}' diperbarui.");
    }

    public function remove(Product $product): RedirectResponse
    {
        $cart = session('polmart_cart', []);
        unset($cart[$product->id]);
        session(['polmart_cart' => $cart]);

        return redirect()
            ->route('anggota.keranjang')
            ->with('success', "'{$product->name}' dihapus dari keranjang.");
    }

    public function clear(): RedirectResponse
    {
        session()->forget('polmart_cart');

        return redirect()
            ->route('anggota.keranjang')
            ->with('success', 'Keranjang belanja berhasil dikosongkan.');
    }

    /**
     * @return array<string, mixed>
     */
    public function buildCartSummary(): array
    {
        $rawCart = session('polmart_cart', []);
        $productIds = array_keys($rawCart);

        $products = empty($productIds)
            ? collect()
            : Product::whereIn('id', $productIds)
                ->where('is_active', true)
                ->get()
                ->keyBy('id');

        $items = [];
        $cleanedCart = [];
        $totalItems = 0;
        $totalAmount = 0.0;
        $warnings = [];

        foreach ($rawCart as $productId => $qty) {
            $product = $products->get((int) $productId);
            if (! $product || $product->stock <= 0) {
                $warnings[] = 'Sebagian barang di keranjang tidak lagi tersedia dan telah dihapus otomatis.';
                continue;
            }

            $qty = max(1, (int) $qty);
            if ($qty > $product->stock) {
                $qty = $product->stock;
                $warnings[] = "Jumlah '{$product->name}' disesuaikan menjadi {$qty} sesuai sisa stok.";
            }

            $unitPrice = (float) $product->price;
            $subtotal = $unitPrice * $qty;

            $cleanedCart[$product->id] = $qty;
            $totalItems += $qty;
            $totalAmount += $subtotal;

            $items[] = [
                'product' => $product,
                'qty' => $qty,
                'unit_price' => $unitPrice,
                'subtotal' => $subtotal,
            ];
        }

        if ($cleanedCart !== $rawCart) {
            session(['polmart_cart' => $cleanedCart]);
        }

        return [
            'items' => $items,
            'totalItems' => $totalItems,
            'totalAmount' => $totalAmount,
            'warnings' => array_unique($warnings),
        ];
    }
}

