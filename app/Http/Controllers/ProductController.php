<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
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

        if ($request->category && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $sortField = $request->sort_field ?? 'created_at';
        $sortDir   = $request->sort_dir ?? 'desc';
        $allowedSorts = ['name', 'sku', 'category', 'price', 'quantity', 'status', 'created_at'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDir);
        }

        $products = $query->paginate(15)->withQueryString();

        $categories = Product::where('user_id', auth()->id())
            ->distinct()
            ->pluck('category')
            ->filter()
            ->values();

        return Inertia::render('Products/Index', [
            'products' => $products,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category', 'status', 'sort_field', 'sort_dir']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Products/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'category'    => 'required|string|max:100',
            'price'       => 'required|numeric|min:0',
            'sku'         => 'required|string|max:100|unique:products,sku',
            'quantity'    => 'required|integer|min:0',
            'status'      => 'required|in:draft,active,inactive',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'image'       => 'nullable|image|max:2048',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['low_stock_threshold'] = $validated['low_stock_threshold'] ?? 10;

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($validated);

        return redirect()->route('products.show', $product)
            ->with('success', 'Product created successfully.');
    }

    public function show(Product $product): Response
    {
        Gate::authorize('view', $product);

        $recentAdjustments = $product->adjustments()
            ->with('user')
            ->latest()
            ->take(10)
            ->get()
            ->map(fn($adj) => [
                'id' => $adj->id,
                'quantity_change' => $adj->quantity_change,
                'quantity_before' => $adj->quantity_before,
                'quantity_after'  => $adj->quantity_after,
                'reason'          => $adj->reason,
                'notes'           => $adj->notes,
                'user_name'       => $adj->user->name,
                'created_at'      => $adj->created_at->format('M d, Y H:i'),
            ]);

        return Inertia::render('Products/Show', [
            'product'            => $product->append('stock_status'),
            'recentAdjustments'  => $recentAdjustments,
        ]);
    }

    public function edit(Product $product): Response
    {
        Gate::authorize('update', $product);

        return Inertia::render('Products/Edit', [
            'product' => $product,
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        Gate::authorize('update', $product);

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'category'    => 'required|string|max:100',
            'price'       => 'required|numeric|min:0',
            'sku'         => "required|string|max:100|unique:products,sku,{$product->id}",
            'quantity'    => 'required|integer|min:0',
            'status'      => 'required|in:draft,active,inactive',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'image'       => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($validated);

        return redirect()->route('products.show', $product)
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        Gate::authorize('delete', $product);

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }

    public function bulkUpdate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_ids'   => 'required|array',
            'product_ids.*' => 'integer',
            'action'        => 'required|in:activate,deactivate,delete,update_category,update_status',
            'value'         => 'nullable|string',
        ]);

        $products = Product::where('user_id', auth()->id())
            ->whereIn('id', $validated['product_ids']);

        match ($validated['action']) {
            'activate'        => $products->update(['status' => 'active']),
            'deactivate'      => $products->update(['status' => 'inactive']),
            'delete'          => $products->delete(),
            'update_category' => $products->update(['category' => $validated['value']]),
            'update_status'   => $products->update(['status' => $validated['value']]),
            default           => null,
        };

        return back()->with('success', 'Bulk action completed.');
    }

    public function export(Request $request)
    {
        $query = Product::where('user_id', auth()->id());

        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }
        if ($request->category) {
            $query->where('category', $request->category);
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }

        $products = $query->get(['name', 'sku', 'category', 'price', 'quantity', 'status', 'created_at']);

        $csv = "Name,SKU,Category,Price,Quantity,Status,Created At\n";
        foreach ($products as $p) {
            $csv .= "\"{$p->name}\",\"{$p->sku}\",\"{$p->category}\",{$p->price},{$p->quantity},{$p->status},{$p->created_at}\n";
        }

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="products.csv"',
        ]);
    }
}
