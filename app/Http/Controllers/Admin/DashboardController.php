<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\StockMutation;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, OrderService $orderService): View
    {
        $orderService->cancelExpiredOrders();

        $topFilter = $request->input('top_filter', 'all') === 'month' ? 'month' : 'all';

        $topSellingQuery = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.status', 'SELESAI');

        if ($topFilter === 'month') {
            $topSellingQuery->whereBetween('orders.transaction_date', [
                now()->startOfMonth(),
                now()->endOfMonth(),
            ]);
        }

        $topSellingProducts = $topSellingQuery
            ->select(
                'products.id',
                'products.sku',
                'products.name',
                'products.category',
                'products.price',
                'products.stock',
                DB::raw('SUM(order_items.qty) as total_sold'),
                DB::raw('SUM(order_items.subtotal) as total_revenue')
            )
            ->groupBy(
                'products.id',
                'products.sku',
                'products.name',
                'products.category',
                'products.price',
                'products.stock'
            )
            ->orderByDesc('total_sold')
            ->limit(10)
            ->get();

        $highestStockProducts = Product::where('is_active', true)
            ->orderByDesc('stock')
            ->orderBy('name')
            ->limit(10)
            ->get();

        $readyPickupOrders = Order::with(['user', 'items.product'])
            ->where('status', 'SIAP_DIAMBIL')
            ->latest('paid_at')
            ->limit(8)
            ->get();

        $recentOrders = Order::with('items')
            ->latest('transaction_date')
            ->limit(6)
            ->get();

        $recentMutations = StockMutation::with(['product', 'user'])
            ->latest()
            ->limit(6)
            ->get();

        $stats = [
            'total_products' => Product::count(),
            'active_products' => Product::where('is_active', true)->count(),
            'low_stock_count' => Product::where('stock', '>', 0)->where('stock', '<=', 10)->count(),
            'out_of_stock_count' => Product::where('stock', '<=', 0)->count(),
            'categories_count' => Product::distinct('category')->count('category'),
            'completed_orders_count' => Order::where('status', 'SELESAI')->count(),
            'total_revenue' => (float) Order::where('status', 'SELESAI')->sum('total_amount'),
            'ready_pickup_count' => Order::where('status', 'SIAP_DIAMBIL')->count(),
            'pending_payment_count' => Order::where('status', 'MENUNGGU_BAYAR')->count(),
        ];

        return view('admin.dashboard', compact(
            'topFilter',
            'topSellingProducts',
            'highestStockProducts',
            'readyPickupOrders',
            'recentOrders',
            'recentMutations',
            'stats'
        ));
    }
}

