<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\ad;
use App\Models\city;
use App\Models\product;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\products_type;
use App\Models\sector;
use App\Models\User;
use App\Models\country;
use App\Models\order;
use App\Models\order_info;
use App\Models\question;
use Database\Seeders\CountriesSeeder;
use Illuminate\Support\Facades\DB;
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
                'cities.name as city_name',
                'cities.lat as city_lat',
                'cities.lng as city_lng',
                'sectors.lat as sector_lat',
                'sectors.lng as sector_lng'
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

        return response([
            'status' => true,
            'message' => 'done Successfully',
            'products' => $var,
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

    public function addCity(Request $request)
    {
        $validatedData = $request->validate([
            'country_id' => 'required',
            'lat' => 'required',
            'lng' => 'required',
            'name' => 'required',
        ]);

        city::create($validatedData);

        $cities = city::join('countries', 'country_id', 'countries.id')
            ->get([
                'cities.id',
                'country_id',
                'countries.name as country_name',
                'cities.name as city_name',
                'lat',
                'lng'
            ]);

        return response()->json([
            'status' => true,
            'message' => 'added Successfully',
            'cities' => $cities,
        ]);
    }

    public function editCity(Request $request)
    {
        $request->validate([
            'city_id' => 'required',
        ]);

        if (!(city::where('id', $request->city_id)->exists())) {
            return response()->json([
                'status' => false,
                'message' => "Wrong City ID"
            ], 200);
        }

        $city = city::find($request->city_id);

        if ($request->has('country_id')) {
            if (!(country::where('id', $request->country_id)->exists())) {
                return response()->json([
                    'status' => false,
                    'message' => "Wrong Country ID"
                ], 200);
            }
        }

        $input = $request->all();

        foreach ($input as $key => $value) {
            if (in_array($key, ['name', 'country_id', 'lat', 'lng']) && !empty($value)) {
                $city->$key = $value;
            }
        }

        $city->save();

        $cities = city::join('countries', 'country_id', 'countries.id')
            ->get([
                'cities.id',
                'country_id',
                'countries.name as country_name',
                'cities.name as city_name',
                'lat',
                'lng'
            ]);

        return response()->json([
            'status' => true,
            'message' => 'edited Successfully',
            'cities' => $cities,
        ]);
    }

    public function editSector(Request $request)
    {
        $request->validate([
            'sector_id' => 'required',
        ]);

        if (!(sector::where('id', $request->sector_id)->exists())) {
            return response()->json([
                'status' => false,
                'message' => "Wrong Sector ID"
            ], 200);
        }

        if ($request->has('city_id')) {
            if (!(city::where('id', $request->city_id)->exists())) {
                return response()->json([
                    'status' => false,
                    'message' => "Wrong City ID"
                ], 200);
            }
        }

        $sector = sector::find($request->sector_id);

        $input = $request->all();

        foreach ($input as $key => $value) {
            if (in_array($key, ['city_id', 'lat', 'lng']) && !empty($value)) {
                $sector->$key = $value;
            }
        }

        $sector->save();

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
            'message' => 'edited Successfully',
            'sectors' => $sectors,
        ]);
    }

    public function showMsgs()
    {
        $msgs = question::get();

        return response()->json([
            'status' => true,
            'messages' => $msgs
        ]);
    }

    public function addAnswer(Request $request)
    {
        $request->validate([
            'question_id' => 'required',
            'answer' => 'required',
        ]);

        if (!(question::where('id', $request->question_id)->exists())) {
            return response()->json([
                'status' => false,
                'message' => "Wrong ID"
            ], 200);
        }

        $question = question::find($request->question_id);
        $question->answer = $request->answer;
        $question->emp_id = auth()->user()->id;
        $question->save();

        $msgs = question::get();

        return response()->json([
            'status' => true,
            'messages' => $msgs
        ]);
    }

    public function createEmpAccount(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required',
            'email' => 'email|required',
            'password' => 'required',
            'phone_no' => 'required',
            'gender' => 'required',
            'birth_date' => 'required',
            'role_id' => 'required',
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

        if ($request->has('role_id') && $request->input('role_id') == 2 && (!$request->has('sector_id'))) {
            return response()->json([
                'status' => false,
                'message' => "sector id is required when registering sector employees."
            ], 200);
        }

        $validatedData['password'] = bcrypt($request->password);

        if ($request->has('img_url')) {
            $image1 = Str::random(32) . "." . $request->img_url->getClientOriginalExtension();
            Storage::disk('public_htmlUsersPhotos')->put($image1, file_get_contents($request->img_url));
            $image1 = asset('api/UsersPhotos/' . $image1);
            $validatedData['img_url'] = $image1;
        }
        if ($request->has('city_id')) {
            $validatedData['city_id'] = $request->city_id;
        }
        if ($request->has('sector_id')) {
            $validatedData['sector_id'] = $request->sector_id;
        }

        $user = User::create($validatedData);

        $user_data = User::where('id', $user->id)->first();

        return response()->json([
            'status' => true,
            'user_data' => $user_data
        ]);
    }

    public function editProduct(Request $request)
    {
        $request->validate([
            'product_id' => 'required',
        ]);

        if (!(product::where('id', $request->product_id)->exists())) {
            return response()->json([
                'status' => false,
                'message' => "Wrong Product ID"
            ], 200);
        }

        if ($request->has('type_id')) {
            if (!(products_type::where('id', $request->type_id)->exists())) {
                return response()->json([
                    'status' => false,
                    'message' => "Wrong type ID"
                ], 200);
            }
        }

        if ((($request->has('price')) && ($request->price < 0)) || (($request->has('quantity')) && ($request->quantity < 0))) {
            return response()->json([
                'status' => false,
                'message' => "Wrong negative value"
            ], 200);
        }

        if (($request->has('discount_rate')) && (($request->discount_rate < 0) || ($request->discount_rate > 100))) {
            return response()->json([
                'status' => false,
                'message' => "Wrong Discount value"
            ], 200);
        }

        $product = product::find($request->product_id);

        $input = $request->all();

        foreach ($input as $key => $value) {
            if (in_array($key, ['name', 'type_id', 'disc', 'discount_rate', 'price', 'quantity'])) {
                $product->$key = $value;
            }
        }

        if ($request->has('img_url')) {
            $image1 = Str::random(32) . "." . $request->img_url->getClientOriginalExtension();
            Storage::disk('public_htmlProducts')->put($image1, file_get_contents($request->img_url));
            $image1 = asset('api/products/' . $image1);
            $product->img_url = $image1;
        }

        $product->save();

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

    public function orderStartWorking($id)
    {
        if (!(order::where('id', $id)->exists())) {
            return response()->json([
                'status' => false,
                'message' => "Wrong order ID"
            ], 200);
        }

        $order = order::find($id);

        if ((auth()->user()->role_id == 2) && (auth()->user()->sector_id != $order->sector_id)) {
            return response()->json([
                'status' => false,
                'message' => "You cant access orders from other sectors"
            ], 200);
        }

        if (in_array($order->status_id, [3, 4, 5, 6])) {
            return response()->json([
                'status' => false,
                'message' => "you cant move directly from this state to the working state , only new => working"
            ], 200);
        }

        $order->status_id = 2;
        $order->emp_id = auth()->user()->id;
        $order->save();

        if (auth()->user()->role_id == 2) {
            $orders = $orders = order::where('orders.sector_id', auth()->user()->sector_id)
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
        } else {
            $orders = order::join('order_statuses as s', 's.id', 'status_id')
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
        }
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
        return response()->json([
            'status' => true,
            'message' => "done successfully",
            'orders' => $orders
        ], 200);
    }

    public function orderEndWorking($id)
    {
        if (!(order::where('id', $id)->exists())) {
            return response()->json([
                'status' => false,
                'message' => "Wrong order ID"
            ], 200);
        }

        $order = order::find($id);

        if ((auth()->user()->role_id == 2) && (auth()->user()->sector_id != $order->sector_id)) {
            return response()->json([
                'status' => false,
                'message' => "You cant access orders from other sectors"
            ], 200);
        }

        if (in_array($order->status_id, [4, 1, 5, 6])) {
            return response()->json([
                'status' => false,
                'message' => "you cant move directly from this state to the working state , only working => ended"
            ], 200);
        }

        $order->status_id = 3;
        $order->save();

        if (auth()->user()->role_id == 2) {
            $orders = $orders = order::where('orders.sector_id', auth()->user()->sector_id)
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
        } else {
            $orders = order::join('order_statuses as s', 's.id', 'status_id')
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
        }
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
        return response()->json([
            'status' => true,
            'message' => "done successfully",
            'orders' => $orders
        ], 200);
    }

    public function orderCancelled($id)
    {
        if (!(order::where('id', $id)->exists())) {
            return response()->json([
                'status' => false,
                'message' => "Wrong order ID"
            ], 200);
        }

        $order = order::find($id);

        if (in_array(auth()->user()->role_id, [3, 4]) && (auth()->user()->id != $order->user_id)) {
            return response()->json([
                'status' => false,
                'message' => "Only order sender or sector employee can cancel orders"
            ], 200);
        }

        if ((auth()->user()->role_id == 2) && (auth()->user()->sector_id != $order->sector_id)) {
            return response()->json([
                'status' => false,
                'message' => "You cant access orders from other sectors"
            ], 200);
        }

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


        if (auth()->user()->role_id == 2) {
            $orders = $orders = order::where('orders.sector_id', auth()->user()->sector_id)
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
        } else if (auth()->user()->role_id == 1) {
            $orders = order::join('order_statuses as s', 's.id', 'status_id')
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
        } else {
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
        }
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
        return response()->json([
            'status' => true,
            'message' => "done successfully",
            'orders' => $orders
        ], 200);
    }

    public function orderStartDeliver($id)
    {
        if (!(order::where('id', $id)->exists())) {
            return response()->json([
                'status' => false,
                'message' => "Wrong order ID"
            ], 200);
        }

        $order = order::find($id);

        if (in_array($order->status_id, [1, 2, 6, 5])) {
            return response()->json([
                'status' => false,
                'message' => "you cant start deliver order before it ended"
            ], 200);
        }

        $order->status_id = 4;
        $order->save();

        $orders = order::where('orders.status_id', 3)->orWhere('orders.status_id', 4)
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

    public function orderEndDeliver($id)
    {
        if (!(order::where('id', $id)->exists())) {
            return response()->json([
                'status' => false,
                'message' => "Wrong order ID"
            ], 200);
        }

        $order = order::find($id);

        if (in_array($order->status_id, [1, 2, 3, 5])) {
            return response()->json([
                'status' => false,
                'message' => "you cant end deliver order before you start it"
            ], 200);
        }

        $order->status_id = 6;
        $order->save();

        $orders = order::where('orders.status_id', 3)->orWhere('orders.status_id', 4)
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

    public function showSectorOrders()
    {
        if (auth()->user()->role_id == 1) {
            return response()->json([
                'status' => false,
                'message' => "this process for sectors employees only"
            ], 200);
        }

        $orders = order::where('orders.sector_id', auth()->user()->sector_id)
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

    public function showEndedOrders()
    {

        $orders = order::where('orders.status_id', 3)->orWhere('orders.status_id', 4)
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

    public function addOrder(Request $request)
    {

        if (auth()->user()->role_id == 1) {
            return response()->json([
                'status' => false,
                'message' => "this process for sectors employees only"
            ], 200);
        }

        $validatedData = $request->validate([
            'lat' => 'required',
            'lng' => 'required',
            'products' => 'required',
        ]);

        $validatedData['user_id'] = auth()->user()->id;
        $validatedData['emp_id'] = auth()->user()->id;
        $validatedData['status_id'] = 2;
        $validatedData['sector_id'] = auth()->user()->sector_id;

        $s = sector::where('id', auth()->user()->sector_id)->get(['lat', 'lng']);

        $theta = $s[0]['lng'] - $request->lng;
        $dist = sin(deg2rad($s[0]['lat'])) * sin(deg2rad($request->lat)) + cos(deg2rad($s[0]['lat'])) * cos(deg2rad($request->lat)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;

        $validatedData['distance'] = $miles * 1.609344;
        $validatedData['delivery_price'] = $validatedData['distance'] * 10;
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

        $orders = order::where('user_id', auth()->user()->id)
            ->join('order_statuses as s', 's.id', 'status_id')
            ->leftjoin('users as d', 'd.id', 'delivery_emp_id')
            ->join('users as u', 'u.id', 'user_id')
            ->leftjoin('users as e', 'e.id', 'emp_id')
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
                'total_price'
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

    public function addCash(Request $request)
    {
        $request->validate([
            'phone_no' => 'required',
            'cash' => 'required',
        ]);

        $user = User::where('phone_no', $request->phone_no)->get();

        User::where('phone_no', $request->phone_no)->update([
            'badget' => $user[0]['badget'] + $request->cash,
        ]);

        $user = User::where('phone_no', $request->phone_no)->get();

        return response([
            'status' => true,
            'message' => 'done successfully',
            'user_data' => $user,
        ], 200);
    }

    public function showAllOrders()
    {

        $orders = order::join('order_statuses as s', 's.id', 'status_id')
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

    public function getReport(Request $request)
    {
        $request->validate([
            'date1' => 'required',
            'date2' => 'required',
        ]);

        $users_count = DB::table('users')
            ->whereDate('created_at', '>=', $request->date1)
            ->whereDate('created_at', '<=', $request->date2)
            ->count();

        $users = DB::table('users as u')
            ->whereDate('created_at', '>=', $request->date1)
            ->whereDate('created_at', '<=', $request->date2)
            ->join('roles as r', 'r.id', 'u.role_id')
            ->leftjoin('cities as c', 'c.id', 'u.city_id')
            ->get([
                'u.id',
                'u.name',
                'sector_id',
                'role_id',
                'r.name as role_name',
                'city_id',
                'c.name as city_name',
                'email',
                'birth_date',
                'password',
                'gender',
                'phone_no',
                'img_url',
                'badget',
                'blocked',
                'created_at',
                'updated_at'
            ]);

        $orders_count = DB::table('orders')
            ->whereDate('created_at', '>=', $request->date1)
            ->whereDate('created_at', '<=', $request->date2)
            ->count();

        $orders = order::whereDate('orders.created_at', '>=', $request->date1)
            ->whereDate('orders.created_at', '<=', $request->date2)
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

        $total_products = 0;
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
            foreach ($products as $p) {
                $total_products += $p['quantity'];
            }
        }

        $total_prices = DB::table('orders')
            ->whereDate('created_at', '>=', $request->date1)
            ->whereDate('created_at', '<=', $request->date2)
            ->sum('total_price');

        $order_prices = DB::table('orders')
            ->whereDate('created_at', '>=', $request->date1)
            ->whereDate('created_at', '<=', $request->date2)
            ->sum('order_price');

        $delivery_prices = DB::table('orders')
            ->whereDate('created_at', '>=', $request->date1)
            ->whereDate('created_at', '<=', $request->date2)
            ->sum('delivery_price');

        return response()->json([
            'status' => true,
            'users_count' => $users_count,
            'users' => $users,
            'orders_count' => $orders_count,
            'orders' => $orders,
            '$orders_prices' => $order_prices,
            '$delivery_prices' => $delivery_prices,
            '$total_prices' => $total_prices,
            '$total_products' => $total_products
        ]);
    }
}
