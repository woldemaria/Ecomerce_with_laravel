<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\InventoryAdjustment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReportsController extends Controller
{
    public function performance(): Response
    {
        $userId = auth()->id();

        $products = Product::where('user_id', $userId)->get();

        $stats = [
            'total_products'  => $products->count(),
            'active_products' => $products->where('status', 'active')->count(),
            'inventory_value' => $products->sum(fn($p) => $p->price * $p->quantity),
            'low_stock_count' => $products->filter(fn($p) => $p->quantity > 0 && $p->quantity <= $p->low_stock_threshold)->count(),
        ];

        $categoryDistribution = $products->groupBy('category')->map(fn($items, $cat) => [
            'category' => $cat ?: 'Uncategorized',
            'count'    => $items->count(),
            'value'    => $items->sum(fn($p) => $p->price * $p->quantity),
        ])->values();

        $statusDistribution = $products->groupBy('status')->map(fn($items, $status) => [
            'status' => $status,
            'count'  => $items->count(),
        ])->values();

        $topProducts = $products->sortByDesc('quantity')->take(5)->values()->map(fn($p) => [
            'id'       => $p->id,
            'name'     => $p->name,
            'sku'      => $p->sku,
            'quantity' => $p->quantity,
            'price'    => $p->price,
            'value'    => $p->price * $p->quantity,
            'status'   => $p->status,
        ]);

        $monthlyTrend = InventoryAdjustment::with('product')
            ->whereHas('product', fn($q) => $q->where('user_id', $userId))
            ->where('created_at', '>=', now()->subMonths(6))
            ->get()
            ->groupBy(fn($adj) => $adj->created_at->format('Y-m'))
            ->map(fn($group, $month) => [
                'month'      => \Carbon\Carbon::createFromFormat('Y-m', $month)->format('M Y'),
                'net_change' => $group->sum('quantity_change'),
            ])
            ->sortBy('month')
            ->values();

        return Inertia::render('Reports/Performance', [
            'stats'                => $stats,
            'categoryDistribution' => $categoryDistribution,
            'statusDistribution'   => $statusDistribution,
            'topProducts'          => $topProducts,
            'monthlyTrend'         => $monthlyTrend,
        ]);
    }

    public function search(Request $request): Response
    {
        $query = Product::where('user_id', auth()->id());

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('sku', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        if ($request->category) $query->where('category', $request->category);
        if ($request->status)   $query->where('status', $request->status);
        if ($request->sku)      $query->where('sku', 'like', "%{$request->sku}%");

        if ($request->min_price) $query->where('price', '>=', $request->min_price);
        if ($request->max_price) $query->where('price', '<=', $request->max_price);
        if ($request->min_qty)   $query->where('quantity', '>=', $request->min_qty);
        if ($request->max_qty)   $query->where('quantity', '<=', $request->max_qty);

        $products = $query->paginate(20)->withQueryString();

        $categories = Product::where('user_id', auth()->id())
            ->distinct()->pluck('category')->filter()->values();

        return Inertia::render('Reports/Search', [
            'products'   => $products,
            'categories' => $categories,
            'filters'    => $request->only([
                'search', 'category', 'status', 'sku',
                'min_price', 'max_price', 'min_qty', 'max_qty'
            ]),
        ]);
    }
}
