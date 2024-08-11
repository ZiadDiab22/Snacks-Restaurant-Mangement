<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\ad;
use App\Models\city;
use App\Models\favourite;
use App\Models\like;
use App\Models\order;
use App\Models\product;
use App\Models\products_type;
use App\Models\question;
use App\Models\sector;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\DB;

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
        $validatedData['role_id'] = 4;

        if ($request->has('img_url')) {
            $image1 = Str::random(32) . "." . $request->img_url->getClientOriginalExtension();
            Storage::disk('public_htmlUsersPhotos')->put($image1, file_get_contents($request->img_url));
            $image1 = asset('api/UsersPhotos/' . $image1);
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

        $msgs = question::where('user_id', auth()->user()->id)->whereNotNull('emp_id')->get();

        return response([
            'status' => true,
            'user_data' => $user,
            'user_messages' => $msgs,
        ], 200);
    }

    public function home()
    {
        $products = product::join('products_types', 'type_id', 'products_types.id')
            ->orderBy('likes', 'desc')->get([
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

    public function sendMsg(Request $request)
    {
        $validatedData = $request->validate([
            'question' => 'required',
        ]);

        $validatedData['user_id'] = auth()->user()->id;
        question::create($validatedData);

        return response([
            'status' => true,
            'message' => "send successfully"
        ], 200);
    }

    public function cancelOrder($id)
    {
        if (!(order::where('id', $id)->exists())) {
            return response([
                'status' => false,
                'message' => 'order not found, wrong id'
            ], 200);
        }

        if (!(order::where('id', $id)->where('user_id', auth()->user()->id)->exists())) {
            return response([
                'status' => false,
                'message' => 'this order isnt for you, wrong id'
            ], 200);
        }

        $order = order::find($id);

        if ($order->status_id == 1) {
            $order->status_id = 4;
            $order->save();
            return response([
                'status' => true,
                'message' => 'done successfully'
            ], 200);
        } else if ($order->status_id == 4) {
            return response([
                'status' => true,
                'message' => 'order already cancelled'
            ], 200);
        } else if ($order->status_id == 2) {
            return response([
                'status' => true,
                'message' => 'The order cant be canceled because its in progress'
            ], 200);
        } else {
            return response([
                'status' => true,
                'message' => 'The order cant be canceled because its already done'
            ], 200);
        }
    }

    public function search(Request $request)
    {
        if ($request->has('name')) {
            $products = product::where('products.name', 'like', '%' . $request->name . '%')->where('visible', 1)->where('quantity', '>', 0)
                ->join('products_types as pt', 'products.type_id', 'pt.id')
                ->get([
                    'products.id',
                    'products.name',
                    'type_id',
                    'pt.name as type',
                    'disc',
                    'discount_rate',
                    'price',
                    'quantity',
                    'img_url',
                    'visible',
                    'likes'
                ]);
        } else if ($request->has('type_id')) {
            $products = product::where('type_id', $request->type_id)->where('visible', 1)->where('quantity', '>', 0)
                ->join('products_types as pt', 'products.type_id', 'pt.id')
                ->get([
                    'products.id',
                    'products.name',
                    'type_id',
                    'pt.name as type',
                    'disc',
                    'discount_rate',
                    'price',
                    'quantity',
                    'img_url',
                    'visible',
                    'likes'
                ]);
        } else {
            $products = product::where('visible', 1)->where('quantity', '>', 0)
                ->join('products_types as pt', 'products.type_id', 'pt.id')
                ->orderBy('likes', 'desc')
                ->get([
                    'products.id',
                    'products.name',
                    'type_id',
                    'pt.name as type',
                    'disc',
                    'discount_rate',
                    'price',
                    'quantity',
                    'img_url',
                    'visible',
                    'likes'
                ]);
        }
        return response([
            'status' => true,
            'products' => $products
        ], 200);
    }

    public function toggleFavourite($id)
    {
        if (!(product::where('id', $id)->exists())) {
            return response([
                'status' => false,
                'message' => 'product not found, wrong product id'
            ], 200);
        }

        if (!(favourite::where('product_id', $id)->where('user_id', auth()->user()->id)->exists())) {
            $fav = new favourite([
                'user_id' => auth()->user()->id,
                'product_id' => $id
            ]);
            $fav->save();
        } else favourite::where('product_id', $id)->where('user_id', auth()->user()->id)->delete();

        return response([
            'status' => true,
            'message' => "done successfully"
        ], 200);
    }

    public function showFavourites()
    {
        $fav = favourite::where('user_id', auth()->user()->id)
            ->join('products', 'products.id', 'user_id')
            ->get(['favourites.id', 'user_id', 'product_id', 'name', 'price', 'img_url', 'likes']);
        foreach ($fav as $f) {
            if (like::where('product_id', $f['product_id'])->where('user_id', auth()->user()->id)->exists())
                $f['is_like'] = true;
            else  $f['is_like'] = false;
        }
        return response([
            'status' => true,
            'message' => $fav
        ], 200);
    }

    public function showCitiesSectors()
    {
        $cities = city::join('countries', 'country_id', 'countries.id')
            ->get([
                'cities.id',
                'country_id',
                'countries.name as country_name',
                'cities.name as city_name',
                'lat',
                'lng'
            ]);

        $sectors = sector::join('cities', 'city_id', 'cities.id')
            ->get([
                'sectors.id',
                'city_id',
                'cities.name as city_name',
                'cities.lat as city_lat',
                'cities.lng as city_lng',
                'sectors.lat as sector_lat',
                'sectors.lng as sector_lng'
            ]);

        return response()->json([
            'status' => true,
            'sectors' => $sectors,
            'cities' => $cities,
        ]);
    }

    public function showSectors()
    {

        $sectors = sector::join('cities', 'city_id', 'cities.id')
            ->get([
                'sectors.id',
                'city_id',
                'cities.name as city_name',
                'cities.lat as city_lat',
                'cities.lng as city_lng',
                'sectors.lat as sector_lat',
                'sectors.lng as sector_lng'
            ]);

        return response()->json([
            'status' => true,
            'sectors' => $sectors,
        ]);
    }

    public function editProfile(Request $request)
    {
        $request->validate([
            'email' => 'email',
        ]);

        $user = user::find(auth()->user()->id);

        $input = $request->all();

        if ($request->has('city_id')) {
            if (!(city::where('id', $request->city_id)->exists())) {
                return response()->json([
                    'status' => false,
                    'message' => "Wrong city ID"
                ], 200);
            }
        }

        if ($request->has('phone_no')) {
            if (User::where('phone_no', $request->phone_no)->exists()) {
                return response()->json([
                    'status' => false,
                    'message' => "phone number is taken"
                ], 200);
            }
        }

        foreach ($input as $key => $value) {
            if (in_array($key, ['name', 'city_id', 'email', 'birth_date', 'phone_no']) && !empty($value)) {
                $user->$key = $value;
            }
        }

        if ($request->has('img_url')) {
            $image1 = Str::random(32) . "." . $request->img_url->getClientOriginalExtension();
            Storage::disk('public_htmlUsersPhotos')->put($image1, file_get_contents($request->img_url));
            $image1 = asset('api/UsersPhotos/' . $image1);
            $user->img_url = $image1;
        }

        $user->save();

        $user_data = User::where('users.id', auth()->user()->id)
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

        return response()->json([
            'status' => true,
            'message' => 'edited Successfully',
            'user_data' => $user_data,
        ]);
    }
}
