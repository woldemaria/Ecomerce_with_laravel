<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\InventoryAdjustment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();
        $products = Product::where('user_id', $user->id);

        $stats = [
            'total_products' => (clone $products)->count(),
            'total_inventory' => (clone $products)->sum('quantity'),
            'active_listings' => (clone $products)->where('status', 'active')->count(),
            'low_stock_count' => (clone $products)->whereColumn('quantity', '<=', 'low_stock_threshold')->where('quantity', '>', 0)->count(),
            'out_of_stock' => (clone $products)->where('quantity', 0)->count(),
            'inventory_value' => (clone $products)->selectRaw('SUM(price * quantity) as total')->value('total') ?? 0,
        ];

        $recentActivity = InventoryAdjustment::with('product')
            ->whereHas('product', fn($q) => $q->where('user_id', $user->id))
            ->latest()
            ->take(8)
            ->get()
            ->map(fn($adj) => [
                'id' => $adj->id,
                'type' => $adj->quantity_change > 0 ? 'restock' : 'sale',
                'product_name' => $adj->product->name,
                'quantity_change' => $adj->quantity_change,
                'reason' => $adj->reason,
                'created_at' => $adj->created_at->diffForHumans(),
            ]);

        $lowStockProducts = Product::where('user_id', $user->id)
            ->whereColumn('quantity', '<=', 'low_stock_threshold')
            ->where('quantity', '>', 0)
            ->orderBy('quantity')
            ->take(5)
            ->get(['id', 'name', 'sku', 'quantity', 'low_stock_threshold', 'status']);

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recentActivity' => $recentActivity,
            'lowStockProducts' => $lowStockProducts,
        ]);
    }
}
