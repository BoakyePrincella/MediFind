<?php

namespace App\Http\Controllers\Api\V1\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ShopProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ShopInventoryController extends Controller
{
    /** All products in this shop owner's inventory */
    public function index(Request $request)
    {
        $shop = $request->user()->shop;

        $inventory = $shop->shopProducts()
            ->with('product.category')
            ->latest()
            ->paginate(20);

        return response()->json($inventory);
    }

    /**
     * Add a product from the global catalogue to this shop.
     * The shop owner picks a product_id and sets their price.
     */
    public function store(Request $request)
    {
        $shop = $request->user()->shop;

        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'price'      => 'sometimes|numeric|min:0',
            'in_stock'   => 'boolean',
            'notes'      => 'nullable|string|max:255',
        ]);

        $exists = $shop->shopProducts()
            ->where('product_id', $data['product_id'])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'This product is already in your inventory.'
            ], 422);
        }

        $shopProduct = $shop->shopProducts()->create($data);

        return response()->json(
            $shopProduct->load('product.category'), 201
        );
    }

    /**
     * Create a new catalogue product and immediately add it to this shop.
     */
    public function storeProduct(Request $request)
    {
        $shop = $request->user()->shop;

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'brand'       => ['nullable', 'string', 'max:100'],
            'category_id' => ['required', 'exists:categories,id'],
            'image'       => ['nullable', 'image', 'max:2048'],
            'price'       => ['required', 'numeric', 'min:0'],
            'in_stock'    => ['boolean'],
            'notes'       => ['nullable', 'string', 'max:255'],
        ]);

        $slug = Product::slugFromName($data['name']);

        if (Product::slugExists($slug)) {
            throw ValidationException::withMessages([
                'name' => ['Product already exists in the catalogue. Please select it from existing products.'],
            ]);
        }

        return DB::transaction(function () use ($request, $shop, $data, $slug) {
            $productData = [
                'name'        => $data['name'],
                'description' => $data['description'] ?? null,
                'brand'       => $data['brand'] ?? null,
                'category_id' => $data['category_id'],
                'is_active'   => true,
                'slug'        => $slug,
            ];

            if ($request->hasFile('image')) {
                $productData['image'] = $request->file('image')
                    ->store('products', 'public');
            }

            $product = Product::create($productData);

            $shopProduct = $shop->shopProducts()->create([
                'product_id' => $product->id,
                'price'      => $data['price'],
                'in_stock'   => $data['in_stock'] ?? true,
                'notes'      => $data['notes'] ?? null,
            ]);

            return response()->json(
                $shopProduct->load('product.category'), 201
            );
        });
    }

    /**
     * Update price, stock status, or notes.
     * Shop owner can only update their own listings.
     */
    public function update(Request $request, ShopProduct $shopProduct)
    {
        // Make sure this listing belongs to this shop owner
        if ($shopProduct->shop_id !== $request->user()->shop->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $data = $request->validate([
            'price'    => ['sometimes', 'numeric', 'min:0'],
            'in_stock' => ['boolean'],
            'notes'    => ['nullable', 'string', 'max:255'],
        ]);

        $shopProduct->update($data);

        return response()->json($shopProduct->fresh('product'));
    }

    /** Remove a product from this shop's inventory */
    public function destroy(Request $request, ShopProduct $shopProduct)
    {
        if ($shopProduct->shop_id !== $request->user()->shop->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $shopProduct->delete();

        return response()->json(['message' => 'Product removed from your inventory.']);
    }
}
