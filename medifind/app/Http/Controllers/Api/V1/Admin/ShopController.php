<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ShopOwnerAccountCreatedMail;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

class ShopController extends Controller
{
    /** List all shops with owner and product count */
    public function index(Request $request)
    {
        $shops = Shop::with('owner')
            ->withCount('shopProducts')
            ->when($request->city, fn($q) => $q->inCity($request->city))
            ->when($request->verified, fn($q) => $q->verified())
            ->latest()
            ->paginate(20);

        return response()->json($shops);
    }

    /**
     * Create a shop AND its owner account in one step.
     * You onboard the shop owner manually —
     * this creates their login credentials too.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            // Owner account details
            'owner_name'          => ['required', 'string', 'max:255'],
            'owner_email'         => ['required', 'email', 'unique:users,email'],
            'owner_password'      => ['nullable', 'string', 'min:8'],
            'owner_phone'         => ['nullable', 'string'],
            // Shop details
            'name'                => ['required', 'string', 'max:200'],
            'description'         => ['nullable', 'string'],
            'phone'               => ['nullable', 'string'],
            'address'             => ['required', 'string'],
            'city'                => ['required', 'in:Accra,Kumasi'],
            'latitude'            => ['nullable', 'numeric'],
            'longitude'           => ['nullable', 'numeric'],
            'offers_delivery'     => ['boolean'],
            'delivery_radius_km'  => ['nullable', 'numeric', 'min:0'],
            'logo'                => ['nullable', 'image', 'max:2048'],
        ]);

        $temporaryPassword = $data['owner_password'] ?? Str::random(12);

        // 1. Create the owner user account
        $owner = User::create([
            'fullname'     => $data['owner_name'],
            'email'    => $data['owner_email'],
            'password' => Hash::make($temporaryPassword),
            'phone'    => $data['owner_phone'] ?? null,
            'role'     => 'shop_owner',
        ]);

        // 2. Handle logo upload
        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('shops', 'public');
        }

        // 3. Create the shop linked to that owner
        $shop = Shop::create([
            'user_id'             => $owner->id,
            'name'                => $data['name'],
            'slug'                => Str::slug($data['name']),
            'description'         => $data['description'] ?? null,
            'phone'               => $data['phone'] ?? null,
            'address'             => $data['address'],
            'city'                => $data['city'],
            'latitude'            => $data['latitude'] ?? null,
            'longitude'           => $data['longitude'] ?? null,
            'logo'                => $logoPath,
            'offers_delivery'     => $data['offers_delivery'] ?? false,
            'delivery_radius_km'  => $data['delivery_radius_km'] ?? null,
            'is_verified'         => false,
            'is_active'           => true,
        ]);

        Mail::to($owner->email)->send(new ShopOwnerAccountCreatedMail(
            owner: $owner,
            temporaryPassword: $temporaryPassword,
            resetToken: Password::broker()->createToken($owner),
        ));

        return response()->json(
            [
                'message' => 'Shop created and login details emailed to the owner.',
                'shop' => $shop->load('owner'),
            ],
            201
        );
    }

    /** Single shop with all details */
    public function show(Shop $shop)
    {
        return response()->json(
            $shop->load(['owner', 'shopProducts.product'])
                 ->loadCount('shopProducts')
        );
    }

    /** Update shop details */
    public function update(Request $request, Shop $shop)
    {
        $data = $request->validate([
            'name'               => ['sometimes', 'string', 'max:200'],
            'description'        => ['nullable', 'string'],
            'phone'              => ['nullable', 'string'],
            'address'            => ['sometimes', 'string'],
            'city'               => ['sometimes', 'in:Accra,Kumasi'],
            'latitude'           => ['nullable', 'numeric'],
            'longitude'          => ['nullable', 'numeric'],
            'offers_delivery'    => ['boolean'],
            'delivery_radius_km' => ['nullable', 'numeric'],
            'is_active'          => ['boolean'],
            'logo'               => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('shops', 'public');
        }

        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $shop->update($data);

        return response()->json($shop->fresh('owner'));
    }

    /**
     * Verify a shop — flips is_verified to true.
     * This is what gives the shop the verified badge on the frontend.
     */
    public function verify(Shop $shop)
    {
        $shop->update(['is_verified' => true]);

        return response()->json([
            'message' => 'Shop verified successfully.',
            'shop'    => $shop,
        ]);
    }

    /** Delete a shop and its owner account */
    public function destroy(Shop $shop)
    {
        $shop->owner->delete(); // cascades to shop via DB

        return response()->json(['message' => 'Shop and owner account deleted.']);
    }
}
