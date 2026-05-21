<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\ShopProduct;
use Illuminate\Http\Request;

class ShopProductController extends Controller
{
    /** All products stocked by a specific shop */
    public function index(Shop $shop)
    {
        $products = $shop->shopProducts()
            ->with('product.category')
            ->get();

        return response()->json($products);
    }

    /** Add a product to a shop's inventory */
    public function store(Request $request, Shop $shop)
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'price'      => ['required', 'numeric', 'min:0'],
            'in_stock'   => ['boolean'],
            'notes'      => ['nullable', 'string', 'max:255'],
        ]);

        // Prevent duplicate — same product in same shop
        $exists = $shop->shopProducts()
            ->where('product_id', $data['product_id'])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'This product is already listed in this shop.'
            ], 422);
        }

        $shopProduct = $shop->shopProducts()->create($data);

        return response()->json(
            $shopProduct->load('product.category'), 201
        );
    }

    /** Update price or stock status for a shop's product */
    public function update(Request $request, Shop $shop, ShopProduct $shopProduct)
    {
        // Make sure this shopProduct actually belongs to this shop
        if ($shopProduct->shop_id !== $shop->id) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $data = $request->validate([
            'price'    => ['sometimes', 'numeric', 'min:0'],
            'in_stock' => ['boolean'],
            'notes'    => ['nullable', 'string', 'max:255'],
        ]);

        $shopProduct->update($data);

        return response()->json($shopProduct->fresh('product'));
    }

    /** Remove a product from a shop */
    public function destroy(Shop $shop, ShopProduct $shopProduct)
    {
        if ($shopProduct->shop_id !== $shop->id) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $shopProduct->delete();

        return response()->json(['message' => 'Product removed from shop.']);
    }
}