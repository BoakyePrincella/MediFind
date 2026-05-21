<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;

class ShopProfileController extends Controller
{
    /**
     * List all verified active shops.
     * Filterable by city, delivery, and nearby coordinates.
     */
    public function index(Request $request)
    {
        $request->validate([
            'city'      => ['nullable', 'in:Accra,Kumasi'],
            'delivery'  => ['nullable', 'boolean'],
            'lat'       => ['nullable', 'numeric'],
            'lng'       => ['nullable', 'numeric'],
            'radius_km' => ['nullable', 'numeric', 'min:1', 'max:50'],
        ]);

        $shops = Shop::active()
            ->verified()
            ->withCount('shopProducts')
            ->when($request->city, fn($q) =>
                $q->inCity($request->city))
            ->when($request->delivery, fn($q) =>
                $q->where('offers_delivery', true))
            ->when(
                $request->lat && $request->lng,
                fn($q) => $q->nearby(
                    $request->lat,
                    $request->lng,
                    $request->radius_km ?? 5
                )
            )
            ->paginate(20);

        return response()->json($shops);
    }

    /**
     * Single shop public profile page.
     * Shows shop info + all in-stock products.
     */
    public function show(string $slug)
    {
        $shop = Shop::where('slug', $slug)
            ->active()
            ->verified()
            ->firstOrFail();

        $products = $shop->shopProducts()
            ->with('product.category')
            ->inStock()
            ->get();

        return response()->json([
            'shop'     => $shop,
            'products' => $products,
        ]);
    }
}