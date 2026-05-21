<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductSearchController extends Controller
{
    /**
     * Homepage: all active top-level categories with children.
     * React uses this to render the category grid.
     */
    public function categories()
    {
        $categories = Category::with('children')
            ->topLevel()
            ->active()
            ->withCount('products')
            ->orderBy('name')
            ->get();

        return response()->json($categories);
    }

    /**
     * Search products by name, brand, or description.
     * Also filterable by category and city.
     * Returns products with a count of how many shops stock them.
     */
    public function search(Request $request)
    {
        $request->validate([
            'q'           => ['nullable', 'string', 'max:100'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'city'        => ['nullable', 'in:Accra,Kumasi'],
        ]);

        $products = Product::active()
            ->with('category')
            ->withCount([
                // Count only shops that are active and verified
                'shopProducts as shops_count' => fn($q) =>
                    $q->whereHas('shop', fn($s) =>
                        $s->active()->verified()
                    )
            ])
            ->when($request->q, fn($q) => $q->search($request->q))
            ->when($request->category_id, fn($q) =>
                $q->where('category_id', $request->category_id))
            ->having('shops_count', '>', 0) // only show products available somewhere
            ->orderByDesc('shops_count')
            ->paginate(20);

        return response()->json($products);
    }

    /**
     * Single product page.
     * Returns the product + every shop that stocks it
     * with their price, stock status, and location.
     * Optionally filter shops by city or distance.
     */
    public function show(Request $request, string $slug)
    {
        $product = Product::where('slug', $slug)
            ->active()
            ->with('category')
            ->firstOrFail();

        // Get all shops stocking this product
        $shopProducts = $product->shopProducts()
            ->with(['shop' => fn($q) =>
                $q->active()->verified()
                  ->when($request->city, fn($s) => $s->inCity($request->city))
            ])
            ->inStock()
            ->get()
            ->filter(fn($sp) => $sp->shop !== null) // remove unverified shops
            ->sortBy('price')
            ->values();

        return response()->json([
            'product'  => $product,
            'shops'    => $shopProducts,
        ]);
    }
}

