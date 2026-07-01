<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\InventoryAdjustment;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class InventoryController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Product::where('user_id', auth()->id());

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('sku', 'like', "%{$request->search}%");
            });
        }

        if ($request->stock_status) {
            match ($request->stock_status) {
                'in_stock'    => $query->whereColumn('quantity', '>', 'low_stock_threshold'),
                'low_stock'   => $query->whereColumn('quantity', '<=', 'low_stock_threshold')->where('quantity', '>', 0),
                'out_of_stock'=> $query->where('quantity', 0),
                default       => null,
            };
        }

        $products = $query->orderBy('quantity')->paginate(15)->withQueryString();

        $products->getCollection()->transform(function ($product) {
            $product->stock_status_label = match(true) {
                $product->quantity === 0                              => 'out_of_stock',
                $product->quantity <= $product->low_stock_threshold  => 'low_stock',
                default                                               => 'in_stock',
            };
            return $product;
        });

        $summary = [
            'in_stock'     => Product::where('user_id', auth()->id())->whereColumn('quantity', '>', 'low_stock_threshold')->count(),
            'low_stock'    => Product::where('user_id', auth()->id())->whereColumn('quantity', '<=', 'low_stock_threshold')->where('quantity', '>', 0)->count(),
            'out_of_stock' => Product::where('user_id', auth()->id())->where('quantity', 0)->count(),
        ];

        return Inertia::render('Inventory/Index', [
            'products' => $products,
            'summary'  => $summary,
            'filters'  => $request->only(['search', 'stock_status']),
        ]);
    }

    public function adjust(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id'     => 'required|exists:products,id',
            'quantity_change'=> 'required|integer|not_in:0',
            'reason'         => 'required|in:restock,sale,damage,return,correction,other',
            'notes'          => 'nullable|string|max:500',
        ]);

        $product = Product::where('user_id', auth()->id())
            ->findOrFail($validated['product_id']);

        $quantityBefore = $product->quantity;
        $quantityAfter  = max(0, $quantityBefore + $validated['quantity_change']);

        $product->update(['quantity' => $quantityAfter]);

        InventoryAdjustment::create([
            'product_id'      => $product->id,
            'user_id'         => auth()->id(),
            'quantity_before' => $quantityBefore,
            'quantity_after'  => $quantityAfter,
            'quantity_change' => $validated['quantity_change'],
            'reason'          => $validated['reason'],
            'notes'           => $validated['notes'] ?? null,
        ]);

        return back()->with('success', 'Inventory adjusted successfully.');
    }

    public function adjustmentScreen(Request $request): Response
    {
        $products = Product::where('user_id', auth()->id())
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'quantity', 'low_stock_threshold', 'status']);

        $selectedProduct = null;
        if ($request->product_id) {
            $selectedProduct = $products->firstWhere('id', (int) $request->product_id);
        }

        return Inertia::render('Inventory/Adjust', [
            'products'        => $products,
            'selectedProduct' => $selectedProduct,
        ]);
    }

    public function lowStock(): Response
    {
        $userId = auth()->id();

        $lowStockProducts = Product::where('user_id', $userId)
            ->whereColumn('quantity', '<=', 'low_stock_threshold')
            ->where('quantity', '>', 0)
            ->orderBy('quantity')
            ->get();

        $outOfStock = Product::where('user_id', $userId)
            ->where('quantity', 0)
            ->orderBy('name')
            ->get();

        $stats = [
            'critical'    => $lowStockProducts->where('quantity', '<=', 5)->count(),
            'low'         => $lowStockProducts->count(),
            'out_of_stock'=> $outOfStock->count(),
            'total_value_at_risk' => $lowStockProducts->sum(fn($p) => $p->price * $p->low_stock_threshold),
        ];

        return Inertia::render('Inventory/LowStock', [
            'lowStockProducts' => $lowStockProducts->map(fn($p) => array_merge($p->toArray(), ['stock_status' => $p->stock_status])),
            'outOfStockProducts' => $outOfStock,
            'stats' => $stats,
        ]);
    }

    public function bulkAdjust(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'adjustments'                  => 'required|array',
            'adjustments.*.product_id'     => 'required|integer|exists:products,id',
            'adjustments.*.quantity_change'=> 'required|integer|not_in:0',
            'reason'                       => 'required|in:restock,sale,damage,return,correction,other',
            'notes'                        => 'nullable|string',
        ]);

        foreach ($validated['adjustments'] as $adj) {
            $product = Product::where('user_id', auth()->id())->find($adj['product_id']);
            if (!$product) continue;

            $before = $product->quantity;
            $after  = max(0, $before + $adj['quantity_change']);
            $product->update(['quantity' => $after]);

            InventoryAdjustment::create([
                'product_id'      => $product->id,
                'user_id'         => auth()->id(),
                'quantity_before' => $before,
                'quantity_after'  => $after,
                'quantity_change' => $adj['quantity_change'],
                'reason'          => $validated['reason'],
                'notes'           => $validated['notes'] ?? null,
            ]);
        }

        return back()->with('success', 'Bulk inventory update completed.');
    }
}
