<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\product;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\products_type;
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
            $image1 = asset('products/' . $image1);
            $validatedData['img_url'] = $image1;
        }

        product::create($validatedData);

        $var = product::join('products_types', 'type_id', 'products_types.id')
            ->get([
                'products.id', 'products.name', 'type_id', 'products_types.name as type_name',
                'discount_rate', 'disc', 'likes', 'price', 'quantity', 'img_url', 'visible'
            ]);

        return response()->json([
            'status' => true,
            'message' => 'added Successfully',
            'products' => $var,
        ]);
    }
}
