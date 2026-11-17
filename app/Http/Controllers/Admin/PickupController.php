<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PickupController extends Controller
{
    public function index(Request $request, OrderService $orderService): View
    {
        $orderService->cancelExpiredOrders();

        $statusFilter = $request->input('status', 'SIAP_DIAMBIL');
        $search = $request->string('search')->trim()->value();

        $query = Order::with(['user', 'items.product'])
            ->latest('transaction_date');

        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        if ($search !== '') {
            $pattern = '%' . mb_strtolower($search) . '%';
            $cleanCode = ltrim($search, '#');
            $query->where(function ($q) use ($pattern, $cleanCode) {
                $q->whereRaw('LOWER(order_code) LIKE ?', [$pattern])
                    ->orWhereRaw('LOWER(customer_name) LIKE ?', [$pattern])
                    ->orWhere('pickup_code', 'LIKE', "%{$cleanCode}%");
            });
        }

        $orders = $query->paginate(15)->withQueryString();

        $stats = [
            'ready_count' => Order::where('status', 'SIAP_DIAMBIL')->count(),
            'completed_count' => Order::where('status', 'SELESAI')->count(),
            'pending_count' => Order::where('status', 'MENUNGGU_BAYAR')->count(),
            'cancelled_count' => Order::where('status', 'DIBATALKAN')->count(),
        ];

        return view('admin.validasi-loket', compact('orders', 'stats', 'statusFilter', 'search'));
    }

    public function verifyByCode(Request $request, OrderService $orderService): RedirectResponse
    {
        $orderService->cancelExpiredOrders();

        $validated = $request->validate([
            'pickup_code' => ['required', 'string', 'max:10'],
            'order_code' => ['nullable', 'string', 'max:35'],
        ]);

        $cleanCode = preg_replace('/[^0-9]/', '', $validated['pickup_code']);

        if (strlen($cleanCode) !== 4) {
            return back()
                ->withInput()
                ->withErrors(['pickup_code' => 'Kode pengambilan harus terdiri dari 4 digit angka.']);
        }

        $query = Order::where('status', 'SIAP_DIAMBIL')
            ->where('pickup_code', $cleanCode);

        if (! empty($validated['order_code'])) {
            $query->where('order_code', strtoupper(trim($validated['order_code'])));
        }

        $order = $query->first();

        if (! $order) {
            return back()
                ->withInput()
                ->withErrors(['pickup_code' => "Pesanan dengan kode pengambilan #{$cleanCode} tidak ditemukan atau belum berstatus SIAP_DIAMBIL."]);
        }

        $order->update([
            'status' => 'SELESAI',
        ]);

        return back()->with(
            'success',
            "Pengambilan pesanan {$order->order_code} (#{$cleanCode}) atas nama {$order->customer_name} berhasil divalidasi dan diselesaikan."
        );
    }

    public function complete(Request $request, Order $order): RedirectResponse
    {
        if ($order->status !== 'SIAP_DIAMBIL') {
            return back()->withErrors([
                'pickup_code' => "Pesanan {$order->order_code} tidak dalam status SIAP_DIAMBIL.",
            ]);
        }

        if ($request->filled('pickup_code')) {
            $cleanInput = preg_replace('/[^0-9]/', '', (string) $request->input('pickup_code'));
            if ($cleanInput !== $order->pickup_code) {
                return back()->withErrors([
                    'pickup_code' => "Kode pengambilan untuk pesanan {$order->order_code} tidak sesuai.",
                ]);
            }
        }

        $order->update([
            'status' => 'SELESAI',
        ]);

        return back()->with(
            'success',
            "Pesanan {$order->order_code} (#{$order->pickup_code}) telah diserahkan dan berstatus SELESAI."
        );
    }

    public function history(Request $request, OrderService $orderService): View
    {
        $orderService->cancelExpiredOrders();

        $statusFilter = $request->string('status')->trim()->value();
        $sourceFilter = $request->string('source')->trim()->value();
        $search = $request->string('search')->trim()->value();

        $query = Order::with(['user', 'items.product'])
            ->latest('transaction_date');

        if ($statusFilter !== '') {
            $query->where('status', $statusFilter);
        }

        if ($sourceFilter !== '') {
            $query->where('source', $sourceFilter);
        }

        if ($search !== '') {
            $pattern = '%' . mb_strtolower($search) . '%';
            $query->where(function ($q) use ($pattern) {
                $q->whereRaw('LOWER(order_code) LIKE ?', [$pattern])
                    ->orWhereRaw('LOWER(customer_name) LIKE ?', [$pattern]);
            });
        }

        $orders = $query->paginate(20)->withQueryString();

        $stats = [
            'total' => Order::count(),
            'completed' => Order::where('status', 'SELESAI')->count(),
            'ready' => Order::where('status', 'SIAP_DIAMBIL')->count(),
            'pending' => Order::where('status', 'MENUNGGU_BAYAR')->count(),
            'cancelled' => Order::where('status', 'DIBATALKAN')->count(),
        ];

        return view('admin.riwayat-transaksi', compact('orders', 'stats', 'statusFilter', 'sourceFilter', 'search'));
    }
}

