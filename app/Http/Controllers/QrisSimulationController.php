<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrisSimulationController extends Controller
{
    public function show(string $order_code, OrderService $orderService): View
    {
        $orderService->cancelExpiredOrders();

        $order = Order::with(['items.product', 'user'])
            ->where('order_code', $order_code)
            ->firstOrFail();

        $qrisUrl = route('qris.pay', ['order_code' => $order->order_code]);
        $qrCodeSvg = QrCode::format('svg')
            ->size(200)
            ->margin(1)
            ->generate($qrisUrl);

        return view('qris.pay', compact('order', 'qrisUrl', 'qrCodeSvg'));
    }

    public function simulateCallback(Request $request, OrderService $orderService): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'order_code' => ['required', 'string', 'exists:orders,order_code'],
        ]);

        $order = Order::where('order_code', $validated['order_code'])->firstOrFail();

        try {
            $updatedOrder = $orderService->confirmQrisPayment($order);
        } catch (RuntimeException $e) {
            if ($request->boolean('_redirect')) {
                return back()->withErrors(['qris' => $e->getMessage()]);
            }

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'order_code' => $order->order_code,
                'status' => $order->fresh()->status,
            ], 422);
        }

        if ($request->boolean('_redirect')) {
            return back()->with('success', "Pembayaran QRIS lunas! Kode Pengambilan: #{$updatedOrder->pickup_code}");
        }

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran QRIS berhasil dikonfirmasi.',
            'order_code' => $updatedOrder->order_code,
            'status' => $updatedOrder->status,
            'pickup_code' => $updatedOrder->pickup_code,
            'paid_at' => $updatedOrder->paid_at?->format('d/m/Y H:i:s'),
        ]);
    }

    public function status(string $order_code, OrderService $orderService): JsonResponse
    {
        $orderService->cancelExpiredOrders();

        $order = Order::where('order_code', $order_code)->firstOrFail();

        $remainingSeconds = 0;
        if ($order->status === 'MENUNGGU_BAYAR' && $order->expires_at) {
            $remainingSeconds = max(0, (int) now()->diffInSeconds($order->expires_at, false));
        }

        return response()->json([
            'order_code' => $order->order_code,
            'status' => $order->status,
            'pickup_code' => $order->pickup_code,
            'paid_at' => $order->paid_at?->format('d/m/Y H:i:s'),
            'expires_at' => $order->expires_at?->toIso8601String(),
            'remaining_seconds' => $remainingSeconds,
        ]);
    }
}

