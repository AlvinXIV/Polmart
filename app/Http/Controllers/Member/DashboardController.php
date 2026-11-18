<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, OrderService $orderService): View
    {
        $orderService->cancelExpiredOrders();

        $user = $request->user();

        $readyOrders = Order::with('items.product')
            ->where('user_id', $user->id)
            ->where('status', 'SIAP_DIAMBIL')
            ->latest('paid_at')
            ->get();

        $pendingOrders = Order::with('items.product')
            ->where('user_id', $user->id)
            ->where('status', 'MENUNGGU_BAYAR')
            ->latest('transaction_date')
            ->get();

        $recentOrders = Order::with('items.product')
            ->where('user_id', $user->id)
            ->latest('transaction_date')
            ->limit(5)
            ->get();

        $featuredProducts = Product::where('is_active', true)
            ->where('stock', '>', 0)
            ->orderByDesc('stock')
            ->limit(4)
            ->get();

        $cartCount = (int) collect(session('polmart_cart', []))->sum();

        $stats = [
            'ready_count' => $readyOrders->count(),
            'pending_count' => $pendingOrders->count(),
            'completed_count' => Order::where('user_id', $user->id)->where('status', 'SELESAI')->count(),
            'cart_count' => $cartCount,
        ];

        return view('anggota.dashboard', compact(
            'readyOrders',
            'pendingOrders',
            'recentOrders',
            'featuredProducts',
            'stats'
        ));
    }
}

