<?php

namespace App\Http\Controllers\Api\V1\Shop;

use App\Http\Controllers\Controller;
use App\Models\ShopProduct;
use Illuminate\Http\Request;

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
