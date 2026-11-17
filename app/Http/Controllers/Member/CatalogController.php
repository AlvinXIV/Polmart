<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(Request $request, OrderService $orderService): View
    {
        $orderService->cancelExpiredOrders();

        $search = $request->string('search')->trim()->value();
        $selectedCategory = $request->string('category')->trim()->value();

        $query = Product::where('is_active', true)
            ->where('stock', '>', 0);

        if ($search !== '') {
            $pattern = '%' . mb_strtolower($search) . '%';
            $query->where(function ($q) use ($pattern) {
                $q->whereRaw('LOWER(name) LIKE ?', [$pattern])
                    ->orWhereRaw('LOWER(sku) LIKE ?', [$pattern])
                    ->orWhereRaw('LOWER(category) LIKE ?', [$pattern]);
            });
        }

        if ($selectedCategory !== '' && $selectedCategory !== 'Semua') {
            $query->where('category', $selectedCategory);
        }

        $products = $query->orderBy('name')->get();

        $categories = Product::where('is_active', true)
            ->where('stock', '>', 0)
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        $cart = session('polmart_cart', []);
        $cartCount = (int) collect($cart)->sum();

        return view('anggota.katalog', compact(
            'products',
            'categories',
            'search',
            'selectedCategory',
            'cart',
            'cartCount'
        ));
    }
}

