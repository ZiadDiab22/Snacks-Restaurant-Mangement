<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\ad;
use App\Models\product;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\products_type;
use App\Models\sector;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function addProductType(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required',
        ]);

        products_type::create($validatedData);
        $types = products_type::get();

        return response([
            'status' => true,
            'message' => 'done Successfully',
            'types' => $types
        ]);
    }

    public function editProductType(Request $request)
    {
        $validatedData = $request->validate([
            'id' => 'required',
            'name' => 'required',
        ]);

        $productType = products_type::find($request->id);
        $productType->fill($validatedData);
        $productType->save();

        $types = products_type::get();

        return response([
            'status' => true,
            'message' => 'done Successfully',
            'types' => $types
        ]);
    }

    public function deleteProductType(Request $request)
    {
        $request->validate([
            'id' => 'required',
        ]);

        products_type::where('id', $request->id)->delete();
        $types = products_type::get();

        return response([
            'status' => true,
            'message' => 'done Successfully',
            'types' => $types
        ], 200);
    }

    public function addProduct(Request $request)
    {

        $validatedData = $request->validate([
            'name' => 'required',
            'type_id' => 'required',
            'disc' => 'required',
            'price' => 'required',
            'quantity' => 'required',
            'img_url' => 'image|mimes:jpg,png,jpeg,gif,webg,svg|max:2048',
        ]);

        if ($request->quantity < 0) {
            return response()->json([
                'status' => false,
                'message' => "quantitiy couldnt be negative value"
            ], 200);
        }
        if ($request->price < 0) {
            return response()->json([
                'status' => false,
                'message' => "price couldnt be negative value"
            ], 200);
        }

        if ($request->has('discount_rate')) {
            $validatedData['discount_rate'] = $request->discount_rate;
        }
        if ($request->has('img_url')) {
            $image1 = Str::random(32) . "." . $request->img_url->getClientOriginalExtension();
            Storage::disk('public_htmlProducts')->put($image1, file_get_contents($request->img_url));
            $image1 = asset('api/products/' . $image1);
            $validatedData['img_url'] = $image1;
        }

        product::create($validatedData);

        $var = product::join('products_types', 'type_id', 'products_types.id')
            ->get([
                'products.id',
                'products.name',
                'type_id',
                'products_types.name as type_name',
                'discount_rate',
                'disc',
                'likes',
                'price',
                'quantity',
                'img_url',
                'visible'
            ]);

        return response()->json([
            'status' => true,
            'message' => 'added Successfully',
            'products' => $var,
        ]);
    }

    public function addSector(Request $request)
    {
        $validatedData = $request->validate([
            'city_id' => 'required',
            'lat' => 'required',
            'lng' => 'required',
        ]);

        sector::create($validatedData);

        $sectors = sector::join('cities', 'city_id', 'cities.id')
            ->get([
                'sectors.id',
                'city_id',
                'cities.name',
                'lat',
                'lng'
            ]);

        return response()->json([
            'status' => true,
            'message' => 'added Successfully',
            'sectors' => $sectors,
        ]);
    }

    public function shareAd(Request $request)
    {
        $validatedData = $request->validate([
            'img_url' => 'image|mimes:jpg,webp,png,jpeg,gif,svg|max:2048',
        ]);

        if ($request->has('img_url')) {
            $image1 = Str::random(32) . "." . $request->img_url->getClientOriginalExtension();
            Storage::disk('public_htmlAds')->put($image1, file_get_contents($request->img_url));

            $image1 = asset('api/Ads/' . $image1);

            $validatedData['img_url'] = $image1;
        }

        if ($request->has('link')) {
            $validatedData['link'] = $request->link;
        }

        if ($request->has('disc')) {
            $validatedData['disc'] = $request->disc;
        }

        ad::create($validatedData);
        $ads = ad::get();

        return response([
            'status' => true,
            'message' => "done successfully",
            'ads' => $ads,
        ], 200);
    }

    public function deleteAd($id)
    {
        if (!(ad::where('id', $id)->exists())) {
            return response([
                'status' => false,
                'message' => 'not found, wrong id'
            ], 200);
        }

        ad::where('id', $id)->delete();
        $ads = ad::get();

        return response([
            'status' => true,
            'message' => "done successfully",
            'ads' => $ads,
        ], 200);
    }

    public function toggleBlockProduct($id)
    {
        if (!(product::where('id', $id)->exists())) {
            return response([
                'status' => false,
                'message' => 'product not found, wrong product id'
            ], 200);
        }

        $product = product::find($id);
        if ($product->visible == 0) $product->visible = 1;
        else $product->visible = 0;
        $product->save();

        return response([
            'status' => true,
            'message' => 'done Successfully',
        ]);
    }

    public function toggleBlockUser($id)
    {
        if (!(User::where('id', $id)->exists())) {
            return response([
                'status' => false,
                'message' => 'user not found, wrong id'
            ], 200);
        }

        $user = User::find($id);
        if ($user->blocked == 0) $user->blocked = 1;
        else $user->blocked = 0;
        $user->save();

        return response([
            'status' => true,
            'message' => 'done Successfully',
        ]);
    }

    public function toggleBlockSector($id)
    {
        if (!(sector::where('id', $id)->exists())) {
            return response([
                'status' => false,
                'message' => 'not found, Wrong id'
            ], 200);
        }

        $sec = sector::find($id);
        if ($sec->blocked == 0) $sec->blocked = 1;
        else $sec->blocked = 0;
        $sec->save();

        return response([
            'status' => true,
            'message' => 'done Successfully',
        ]);
    }
}
