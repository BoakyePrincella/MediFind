<?php

namespace App\Http\Controllers\Api\V1\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
//use Illuminate\Support\Str;

class ShopDashboardController extends Controller
{
    /**
     * Return the authenticated shop owner's shop.
     * This is what the dashboard loads on first visit.
     */
    public function show(Request $request)
    {
        $shop = $request->user()->shop;

        if (! $shop) {
            return response()->json([
                'message' => 'No shop linked to this account.'
            ], 404);
        }

        return response()->json(
            $shop->loadCount('shopProducts')
        );
    }

    /**
     * Shop owner updates their own shop profile.
     * They cannot change city or verification status — only admin can.
     */
    public function update(Request $request)
    {
        $shop = $request->user()->shop;

        if (! $shop) {
            return response()->json(['message' => 'No shop found.'], 404);
        }

        $data = $request->validate([
            'description'        => 'nullable|string',
            'phone'              => 'nullable|string|max:20',
            'address'            => 'nullable|string',
            'latitude'           => 'nullable|numeric',
            'longitude'          => 'nullable|numeric',
            'offers_delivery'    => 'boolean',
            'delivery_radius_km' => 'nullable|numeric|min:0',
            'logo'               => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')
                ->store('shops', 'public');
        }

        $shop->update($data);

        return response()->json($shop->fresh());
    }
}
