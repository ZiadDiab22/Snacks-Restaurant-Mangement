<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\ad;
use App\Models\city;
use App\Models\favourite;
use App\Models\like;
use App\Models\order;
use App\Models\order_info;
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
        $validatedData['role_id'] = 3;

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

        $msgs = question::where('user_id', auth()->user()->id)->get();

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

        if (in_array($order->status_id, [2, 3, 4, 6])) {
            return response()->json([
                'status' => false,
                'message' => "you cant cancel order after start of working on it"
            ], 200);
        }

        $order->status_id = 5;
        $order->save();

        $user = user::find($order->user_id);
        $user->badget += $order->total_price;
        $user->save();

        $orders = order::where('orders.user_id', auth()->user()->id)
            ->join('order_statuses as s', 's.id', 'status_id')
            ->leftjoin('users as d', 'd.id', 'delivery_emp_id')
            ->join('users as u', 'u.id', 'user_id')
            ->leftjoin('users as e', 'e.id', 'emp_id')
            ->orderBy('orders.created_at', 'desc')
            ->get([
                'orders.id as order_id',
                'delivery_emp_id',
                'd.name as delivery_emp_name',
                'user_id',
                'u.name as user_name',
                'u.email as user_email',
                'u.birth_date as user_birth_date',
                'u.gender as user_gender',
                'u.phone_no as user_phone_no',
                'u.img_url as user_img_url',
                'emp_id',
                'e.name as emp_name',
                'orders.sector_id',
                'status_id',
                's.name as status_name',
                'lat',
                'lng',
                'distance',
                'delivery_price',
                'order_price',
                'total_price',
                'orders.created_at',
                'orders.updated_at'
            ]);

        foreach ($orders as $o) {
            $products = order_info::where('order_id', $o['order_id'])
                ->join('products as p', 'product_id', 'p.id')
                ->join('products_types as t', 'type_id', 't.id')
                ->get([
                    'product_id',
                    'order_infos.quantity',
                    'p.name',
                    'disc',
                    'price',
                    'discount_rate',
                    'likes',
                    'type_id',
                    't.name as type'
                ]);
            $o['products'] = $products;
        }

        return response([
            'status' => true,
            'orders' => $orders
        ], 200);
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
            ->join('products', 'products.id', 'product_id')
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

    public function showProductData($id)
    {
        if (!(product::where('id', $id)->exists())) {
            return response()->json([
                'status' => false,
                'message' => "Wrong Product ID"
            ], 200);
        }

        $products = product::where('products.id', $id)->join('products_types', 'type_id', 'products_types.id')
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

        $products2 = product::where('products.id', '!=', $id)->where('products.type_id', $products[0]['type_id'])
            ->join('products_types', 'type_id', 'products_types.id')
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

        return response([
            'status' => true,
            'product' => $products,
            'products with same type' => $products2,
        ], 200);
    }

    public function sendOrder(Request $request)
    {
        $validatedData = $request->validate([
            'lat' => 'required',
            'lng' => 'required',
            'products' => 'required',
        ]);

        $validatedData['user_id'] = auth()->user()->id;
        $validatedData['status_id'] = 1;

        $sec = sector::get(['id', 'lat', 'lng']);

        $distances = [];
        foreach ($sec as $s) {
            $theta = $s['lng'] - $request->lng;
            $dist = sin(deg2rad($s['lat'])) * sin(deg2rad($request->lat)) + cos(deg2rad($s['lat'])) * cos(deg2rad($request->lat)) * cos(deg2rad($theta));
            $dist = acos($dist);
            $dist = rad2deg($dist);
            $miles = $dist * 60 * 1.1515;
            $distance = $miles * 1.609344;
            $distances[$s['id']] = $distance;
        }

        arsort($distances);
        $distances = array_reverse($distances, true);

        $validatedData['distance'] = current($distances);
        $validatedData['sector_id'] = key($distances);
        $validatedData['delivery_price'] = current($distances) * 10;
        $validatedData['order_price'] = 0;
        $validatedData['total_price'] = 0;
        $order = order::create($validatedData);

        $sum = 0;
        foreach ($request->products as $p) {
            DB::table('order_infos')->insert([
                'order_id' => $order->id,
                'product_id' => $p['id'],
                'quantity' => $p['quantity'],
            ]);

            $pr = product::find($p['id']);
            $sum += $p['quantity'] * ($pr->price * (100 - $pr->discount_rate) / 100);
        }

        $order->order_price = $sum;
        $order->total_price = $sum + $order->delivery_price;
        $order->save();

        $user = user::find(auth()->user()->id);
        if ($order->total_price > $user->badget) {
            order::where('id', $order->id)->delete();
            return response([
                'status' => false,
                'message' => 'you dont have enough money'
            ], 200);
        } else {
            $user->badget -= $order->total_price;
            $user->save();
        }

        $orders = order::where('user_id', auth()->user()->id)
            ->join('order_statuses as s', 's.id', 'status_id')
            ->leftjoin('users as d', 'd.id', 'delivery_emp_id')
            ->join('users as u', 'u.id', 'user_id')
            ->leftjoin('users as e', 'e.id', 'emp_id')
            ->orderBy('orders.created_at', 'desc')
            ->get([
                'orders.id as order_id',
                'delivery_emp_id',
                'd.name as delivery_emp_name',
                'user_id',
                'u.name as user_name',
                'u.email as user_email',
                'u.birth_date as user_birth_date',
                'u.gender as user_gender',
                'u.phone_no as user_phone_no',
                'u.img_url as user_img_url',
                'emp_id',
                'e.name as emp_name',
                'orders.sector_id',
                'status_id',
                's.name as status_name',
                'lat',
                'lng',
                'distance',
                'delivery_price',
                'order_price',
                'total_price',
                'orders.created_at',
                'orders.updated_at'
            ]);

        foreach ($orders as $o) {
            $products = order_info::where('order_id', $o['order_id'])
                ->join('products as p', 'product_id', 'p.id')
                ->join('products_types as t', 'type_id', 't.id')
                ->get([
                    'product_id',
                    'order_infos.quantity',
                    'p.name',
                    'disc',
                    'price',
                    'discount_rate',
                    'likes',
                    'type_id',
                    't.name as type'
                ]);
            $o['products'] = $products;
        }

        return response([
            'status' => true,
            'message' => 'done successfully',
            'orders' => $orders
        ], 200);
    }

    public function showUserOrders()
    {

        $orders = order::where('orders.user_id', auth()->user()->id)
            ->join('order_statuses as s', 's.id', 'status_id')
            ->leftjoin('users as d', 'd.id', 'delivery_emp_id')
            ->join('users as u', 'u.id', 'user_id')
            ->leftjoin('users as e', 'e.id', 'emp_id')
            ->orderBy('orders.created_at', 'desc')
            ->get([
                'orders.id as order_id',
                'delivery_emp_id',
                'd.name as delivery_emp_name',
                'user_id',
                'u.name as user_name',
                'u.email as user_email',
                'u.birth_date as user_birth_date',
                'u.gender as user_gender',
                'u.phone_no as user_phone_no',
                'u.img_url as user_img_url',
                'emp_id',
                'e.name as emp_name',
                'orders.sector_id',
                'status_id',
                's.name as status_name',
                'lat',
                'lng',
                'distance',
                'delivery_price',
                'order_price',
                'total_price',
                'orders.created_at',
                'orders.updated_at'
            ]);

        foreach ($orders as $o) {
            $products = order_info::where('order_id', $o['order_id'])
                ->join('products as p', 'product_id', 'p.id')
                ->join('products_types as t', 'type_id', 't.id')
                ->get([
                    'product_id',
                    'order_infos.quantity',
                    'p.name',
                    'disc',
                    'price',
                    'discount_rate',
                    'likes',
                    'type_id',
                    't.name as type'
                ]);
            $o['products'] = $products;
        }

        return response([
            'status' => true,
            'orders' => $orders
        ], 200);
    }
}
