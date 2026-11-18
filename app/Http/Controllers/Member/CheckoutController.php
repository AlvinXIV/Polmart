<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Http\Controllers\QrisSimulationController;
use App\Models\Order;
use App\Models\Product;
use App\Services\OrderService;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use RuntimeException;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class CheckoutController extends Controller
{
    public function index(CartController $cartController): View|RedirectResponse
    {
        $cartData = $cartController->buildCartSummary();

        if (empty($cartData['items'])) {
            return redirect()
                ->route('anggota.katalog')
                ->withErrors(['cart' => 'Keranjang belanja masih kosong. Pilih barang terlebih dahulu.']);
        }

        return view('anggota.checkout', $cartData);
    }

    public function store(Request $request, StockService $stockService): RedirectResponse
    {
        $cart = session('polmart_cart', []);
        if (empty($cart)) {
            return redirect()
                ->route('anggota.katalog')
                ->withErrors(['cart' => 'Keranjang belanja kosong. Tidak ada barang untuk diproses.']);
        }

        $productIds = array_map('intval', array_keys($cart));
        sort($productIds);

        try {
            $order = DB::transaction(function () use ($request, $cart, $productIds, $stockService) {
                $user = $request->user();
                $now = now();
                $totalAmount = 0.0;

                $order = Order::create([
                    'order_code' => Order::generateOrderCode(),
                    'user_id' => $user->id,
                    'customer_name' => $user->name,
                    'source' => 'web',
                    'status' => 'MENUNGGU_BAYAR',
                    'total_amount' => 0,
                    'expires_at' => $now->copy()->addMinutes(15),
                    'transaction_date' => $now,
                ]);

                foreach ($productIds as $productId) {
                    $qty = (int) ($cart[$productId] ?? 0);
                    if ($qty < 1) {
                        continue;
                    }

                    $product = Product::whereKey($productId)
                        ->lockForUpdate()
                        ->first();

                    if (! $product || ! $product->is_active) {
                        throw new RuntimeException('Salah satu barang yang dipilih sudah tidak tersedia.');
                    }

                    if ($product->stock < $qty) {
                        throw new RuntimeException("Stok barang '{$product->name}' tidak mencukupi (tersisa {$product->stock}).");
                    }

                    $unitPrice = (float) $product->price;
                    $subtotal = $unitPrice * $qty;
                    $totalAmount += $subtotal;

                    $costPrice = (float) ($product->cost_price ?? 0);
                    $subtotalCost = $costPrice * $qty;

                    $order->items()->create([
                        'product_id' => $product->id,
                        'qty' => $qty,
                        'unit_price' => $unitPrice,
                        'subtotal' => $subtotal,
                        'cost_price' => $costPrice,
                        'subtotal_cost' => $subtotalCost,
                    ]);

                    $stockService->recordMutation(
                        product: $product,
                        type: 'out',
                        qty: $qty,
                        userId: $user->id,
                        reference: $order->order_code,
                        notes: 'Reservasi stok checkout pesanan anggota'
                    );
                }

                if ($totalAmount <= 0 && $order->items()->count() === 0) {
                    throw new RuntimeException('Tidak ada item valid dalam keranjang.');
                }

                $order->update([
                    'total_amount' => $totalAmount,
                ]);

                return $order;
            });
        } catch (RuntimeException $e) {
            return redirect()
                ->route('anggota.keranjang')
                ->withErrors(['checkout' => $e->getMessage()]);
        }

        session()->forget('polmart_cart');

        return redirect()
            ->route('anggota.pembayaran', ['order_code' => $order->order_code])
            ->with('success', 'Pesanan berhasil dibuat!');
    }

    public function payment(Request $request, OrderService $orderService, ?string $order_code = null): View|RedirectResponse
    {
        $orderService->cancelExpiredOrders();

        $user = $request->user();

        $query = Order::with('items.product')
            ->where('user_id', $user->id);

        if ($order_code) {
            $order = $query->where('order_code', $order_code)->firstOrFail();
        } else {
            $order = (clone $query)
                ->whereIn('status', ['MENUNGGU_BAYAR', 'SIAP_DIAMBIL'])
                ->latest('transaction_date')
                ->first()
                ?? $query->latest('transaction_date')->first();

            if (! $order) {
                return redirect()
                    ->route('anggota.katalog')
                    ->withErrors(['order' => 'Belum ada pesanan aktif untuk dibayar.']);
            }
        }

        $qrisUrl = QrisSimulationController::resolvePublicQrisUrl($request, $order);
        $qrCodeSvg = QrCode::format('svg')
            ->size(220)
            ->margin(1)
            ->generate($qrisUrl);

        return view('anggota.pembayaran', compact('order', 'qrisUrl', 'qrCodeSvg'));
    }

    public function orders(Request $request, OrderService $orderService): View
    {
        $orderService->cancelExpiredOrders();

        $user = $request->user();

        $orders = Order::with('items.product')
            ->where('user_id', $user->id)
            ->latest('transaction_date')
            ->paginate(10);

        $selectedCode = $request->string('order_code')->trim()->value();
        $selectedOrder = null;

        if ($selectedCode !== '') {
            $selectedOrder = Order::with('items.product')
                ->where('user_id', $user->id)
                ->where('order_code', $selectedCode)
                ->first();
        }

        if (! $selectedOrder) {
            $selectedOrder = $orders->first();
        }

        return view('anggota.pesanan', compact('orders', 'selectedOrder'));
    }
}

