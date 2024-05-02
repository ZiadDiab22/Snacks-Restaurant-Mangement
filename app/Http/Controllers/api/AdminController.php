<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
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
}
