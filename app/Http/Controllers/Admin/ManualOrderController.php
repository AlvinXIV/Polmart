<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use RuntimeException;

class ManualOrderController extends Controller
{
    public function index(): View
    {
        $orders = Order::with(['user', 'items.product'])
            ->where('source', 'manual_admin')
            ->latest('transaction_date')
            ->paginate(15);

        return view('admin.manual-orders.index', compact('orders'));
    }

    public function create(): View
    {
        $members = User::where('role', 'member')
            ->orderBy('name')
            ->get(['id', 'member_code', 'name']);

        $products = Product::where('is_active', true)
            ->where('stock', '>', 0)
            ->orderBy('name')
            ->get(['id', 'sku', 'name', 'price', 'stock']);

        return view('admin.manual-orders.create', compact('members', 'products'));
    }

    public function store(Request $request, StockService $stockService): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['nullable', 'exists:users,id'],
            'customer_name' => ['nullable', 'string', 'max:120'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
        ]);

        $member = ! empty($validated['user_id'])
            ? User::find($validated['user_id'])
            : null;

        $customerName = trim((string) ($validated['customer_name'] ?? ''));
        if ($customerName === '' && $member) {
            $customerName = $member->name;
        }
        if ($customerName === '') {
            $customerName = 'Pelanggan Walk-in';
        }

        // Gabungkan kuantitas jika produk yang sama dipilih di beberapa baris
        $aggregatedItems = [];
        foreach ($validated['items'] as $row) {
            $productId = (int) $row['product_id'];
            $qty = (int) $row['qty'];
            $aggregatedItems[$productId] = ($aggregatedItems[$productId] ?? 0) + $qty;
        }

        try {
            $order = DB::transaction(function () use ($request, $member, $customerName, $aggregatedItems, $stockService) {
                $now = now();
                $totalAmount = 0;

                $order = Order::create([
                    'order_code' => Order::generateOrderCode(),
                    'user_id' => $member?->id,
                    'customer_name' => $customerName,
                    'source' => 'manual_admin',
                    'status' => 'SELESAI',
                    'total_amount' => 0,
                    'paid_at' => $now,
                    'transaction_date' => $now,
                ]);

                foreach ($aggregatedItems as $productId => $qty) {
                    $product = Product::findOrFail($productId);

                    if (! $product->is_active) {
                        throw new RuntimeException("Barang '{$product->name}' sedang nonaktif.");
                    }

                    $unitPrice = (float) $product->price;
                    $subtotal = $unitPrice * $qty;
                    $totalAmount += $subtotal;

                    $order->items()->create([
                        'product_id' => $product->id,
                        'qty' => $qty,
                        'unit_price' => $unitPrice,
                        'subtotal' => $subtotal,
                    ]);

                    $stockService->recordMutation(
                        product: $product,
                        type: 'out',
                        qty: $qty,
                        userId: $request->user()->id,
                        reference: $order->order_code,
                        notes: "Pesanan manual walk-in ({$customerName})"
                    );
                }

                $order->update(['total_amount' => $totalAmount]);

                return $order;
            });
        } catch (RuntimeException $e) {
            return back()
                ->withInput()
                ->withErrors(['items' => $e->getMessage()]);
        }

        return redirect()
            ->route('admin.manual-orders.show', $order)
            ->with('success', "Pesanan walk-in {$order->order_code} berhasil disimpan dengan status SELESAI.");
    }

    public function show(Order $manualOrder): View
    {
        abort_unless($manualOrder->source === 'manual_admin', 404);

        $manualOrder->load(['user', 'items.product']);

        return view('admin.manual-orders.show', ['order' => $manualOrder]);
    }
}