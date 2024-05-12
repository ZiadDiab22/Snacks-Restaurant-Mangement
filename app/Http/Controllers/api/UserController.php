<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\ad;
use App\Models\like;
use App\Models\product;
use App\Models\products_type;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required',
            'email' => 'email|required',
            'password' => 'required',
            'phone_no' => 'required',
            'gender' => 'required',
            'birth_date' => 'required',
        ]);

        if (User::where('email', $request->email)->exists()) {
            return response()->json([
                'status' => false,
                'message' => "email is taken"
            ], 200);
        }

        if (User::where('phone_no', $request->phone_no)->exists()) {
            return response()->json([
                'status' => false,
                'message' => "phone number is taken"
            ], 200);
        }

        $validatedData['password'] = bcrypt($request->password);
        $validatedData['role_id'] = 3;

        if ($request->has('img_url')) {
            $image1 = Str::random(32) . "." . $request->img_url->getClientOriginalExtension();
            Storage::disk('public_htmlUsersPhotos')->put($image1, file_get_contents($request->img_url));
            $image1 = asset('UsersPhotos/' . $image1);
            $validatedData['img_url'] = $image1;
        }
        if ($request->has('sector_id')) {
            $validatedData['sector_id'] = $request->sector_id;
        }
        if ($request->has('city_id')) {
            $validatedData['city_id'] = $request->city_id;
        }

        $user = User::create($validatedData);

        $accessToken = $user->createToken('authToken')->accessToken;

        $user_data = User::where('id', $user->id)->first();

        return response()->json([
            'status' => true,
            'access_token' => $accessToken,
            'user_data' => $user_data
        ]);
    }

    public function login(Request $request)
    {
        $loginData = $request->validate([
            'password' => 'required',
            'email' => 'required'
        ]);

        if (!Auth::guard('web')->attempt(['password' => $loginData['password'], 'email' => $loginData['email']])) {
            return response()->json(['status' => false, 'message' => 'Invalid User'], 404);
        }

        $accessToken = auth()->user()->createToken('authToken')->accessToken;

        $user_data = User::where('email', $request->email)->first();

        return response()->json([
            'status' => true,
            'access_token' => $accessToken,
            'user_data' => $user_data
        ]);
    }

    public function logout(Request $request)
    {
        $token = $request->user()->token();
        $token->revoke();
        return response()->json([
            'status' => true,
            'message' => "User logged out successfully"
        ]);
    }

    public function showProductTypes()
    {
        $var = products_type::get();
        return response([
            'status' => true,
            'types' => $var
        ], 200);
    }

    public function showProducts()
    {
        $products = product::join('products_types', 'type_id', 'products_types.id')
            ->get([
                'products.id', 'products.name', 'type_id', 'products_types.name as type_name',
                'discount_rate', 'disc', 'likes', 'price', 'quantity', 'img_url', 'visible'
            ]);

        $types = products_type::get();

        return response([
            'status' => true,
            'products' => $products,
            'products_types' => $types
        ], 200);
    }

    public function showProductsByLikes()
    {
        $products = product::join('products_types', 'type_id', 'products_types.id')
            ->orderBy('likes', 'desc')->get([
                'products.id', 'products.name', 'type_id', 'products_types.name as type_name',
                'discount_rate', 'disc', 'likes', 'price', 'quantity', 'img_url', 'visible'
            ]);

        $types = products_type::get();

        return response([
            'status' => true,
            'products' => $products,
            'products_types' => $types
        ], 200);
    }

    public function profile()
    {
        $user = User::where('users.id', auth()->user()->id)
            ->leftjoin('cities', 'city_id', 'cities.id')
            ->join('roles', 'role_id', 'roles.id')
            ->get([
                'users.id',
                'users.name',
                'sector_id',
                'role_id',
                'roles.name as role_name',
                'city_id',
                'cities.name as city_name',
                'email',
                'birth_date',
                'gender',
                'phone_no',
                'img_url',
                'badget',
                'created_at',
                'updated_at'
            ]);

        return response([
            'status' => true,
            'user' => $user,
        ], 200);
    }

    public function home()
    {
        $products = product::join('products_types', 'type_id', 'products_types.id')
            ->orderBy('likes', 'desc')->get([
                'products.id', 'products.name', 'type_id', 'products_types.name as type_name',
                'discount_rate', 'disc', 'likes', 'price', 'quantity', 'img_url', 'visible'
            ]);

        $types = products_type::get();

        $ads = ad::get();

        return response([
            'status' => true,
            'ads' => $ads,
            'products_types' => $types,
            'best_products' => $products,
        ], 200);
    }

    public function toggleLike($id)
    {
        if (!(product::where('id', $id)->exists())) {
            return response([
                'status' => false,
                'message' => 'product not found, wrong product id'
            ], 200);
        }

        if (!(like::where('product_id', $id)->where('user_id', auth()->user()->id)->exists())) {
            $fav = new like([
                'user_id' => auth()->user()->id,
                'product_id' => $id
            ]);
            $fav->save();
        } else like::where('product_id', $id)->where('user_id', auth()->user()->id)->delete();

        return response([
            'status' => true,
            'message' => "done successfully"
        ], 200);
    }
}
