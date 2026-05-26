<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    /** Paginated list with category and shop count */
    public function index(Request $request)
    {
        $products = Product::with('category')
            ->withCount('shopProducts')   // how many shops stock it
           ->when($request->search(), fn($q) => $q->search($request->search))
            ->when($request->category_id, fn($q) =>
                $q->where('category_id', $request->category_id))
            ->latest()
            ->paginate(20);

        return response()->json($products);
    }

    /** Create a product in the global catalogue */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'brand'       => ['nullable', 'string', 'max:100'],
            'category_id' => ['required', 'exists:categories,id'],
            'image'       => ['nullable', 'image', 'max:2048'],
            'is_active'   => ['boolean'],
        ]);

        $data['slug'] = Product::slugFromName($data['name']);
        $this->ensureProductSlugIsAvailable($data['slug']);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')
                ->store('products', 'public');
        }

        $product = Product::create($data);

        return response()->json(
            $product->load('category'), 201
        );
    }

    /** Single product detail */
    public function show(Product $product)
    {
        return response()->json(
            $product->load(['category', 'shopProducts.shop'])
        );
    }

    /** Update a product */
    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name'        => ['sometimes', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'brand'       => ['nullable', 'string', 'max:100'],
            'category_id' => ['sometimes', 'exists:categories,id'],
            'image'       => ['nullable', 'image', 'max:2048'],
            'is_active'   => ['boolean'],
        ]);

        if (isset($data['name'])) {
            $data['slug'] = Product::slugFromName($data['name']);
            $this->ensureProductSlugIsAvailable($data['slug'], $product);
        }

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')
                ->store('products', 'public');
        }

        $product->update($data);

        return response()->json($product->fresh('category'));
    }

    /** Delete — only if no shops stock it */
    public function destroy(Product $product)
    {
        if ($product->shopProducts()->exists()) {
            return response()->json([
                'message' => 'Cannot delete a product that shops are currently stocking.'
            ], 422);
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted.']);
    }

    private function ensureProductSlugIsAvailable(string $slug, ?Product $product = null): void
    {
        if (! Product::slugExists($slug, $product?->id)) {
            return;
        }

        throw ValidationException::withMessages([
            'name' => ['Product already exists.'],
        ]);
    }
}
