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
use App\Models\question;
use Database\Seeders\CountriesSeeder;
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

        if (in_array($order->status_id, [3, 4, 5])) {
            return response()->json([
                'status' => false,
                'message' => "you cant move directly from this state to the working state , only new => working"
            ], 200);
        }

        $order->status_id = 2;
        $order->save();
        return response()->json([
            'status' => true,
            'message' => "done successfully"
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

        if (in_array($order->status_id, [3, 1, 5])) {
            return response()->json([
                'status' => false,
                'message' => "you cant move directly from this state to the working state , only working => under delivery"
            ], 200);
        }

        $order->status_id = 4;
        $order->save();
        return response()->json([
            'status' => true,
            'message' => "done successfully"
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

        if ((auth()->user()->role_id == 2) && (auth()->user()->sector_id != $order->sector_id)) {
            return response()->json([
                'status' => false,
                'message' => "You cant access orders from other sectors"
            ], 200);
        }

        if (in_array($order->status_id, [2, 3, 4])) {
            return response()->json([
                'status' => false,
                'message' => "you cant cancel order after start of working on it"
            ], 200);
        }

        $order->status_id = 5;
        $order->save();
        return response()->json([
            'status' => true,
            'message' => "done successfully"
        ], 200);
    }
}
