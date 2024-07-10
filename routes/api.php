<?php

use App\Http\Controllers\PaypalController;
use App\Models\Address;
use App\Models\Amenity;
use App\Models\Category;
use App\Models\Listing;
use App\Models\ListingImage;
use App\Models\Order;
use App\Models\Privacy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Password;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;
use App\Events\PusherBroadcast;
use App\Models\Message;
use App\Models\MessageContent;
use App\Models\Wishlist;
use Carbon\Carbon;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

Configuration::instance('cloudinary://892774692195329:XMDRLriA45tFiLsegzoMwCrlTok@dqsfwus9c?secure=true');

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::prefix('v1')->group(function () {

    Route::get('/rooms', function () {

        $user = auth('sanctum')->user();
        $yearNow = Carbon::now()->year;
        $monthNow = Carbon::now()->month;
        $categoryId = request()->category_id;
        $limit = request()->limit;
        $listings = Listing::where('status', 'confirm')
            ->whereHas('user', function ($query) {
                $query->where('verify_account', 1);
            })
            ->whereHas('category', function ($query) use ($categoryId) {
                if ($categoryId) {
                    $query->where('category_id', $categoryId);
                } else {
                    $query->where('category_id', '!=', null);
                }
            })
            ->with([
                'orders' =>  function ($query) use ($yearNow, $monthNow) {
                    $query->whereYear('check_in', '>=', $yearNow)
                        ->whereMonth('check_in', '>=', $monthNow)
                        ->select('check_in', 'check_out', 'listing_id');
                }
            ])
            ->with('images:id,image,listing_id')
            ->with('address')
            ->with([
                'wishlist' =>  function ($query) use ($user) {
                    if ($user) {
                        $query->where('user_id', $user->id);
                    } else {
                        $query->where('user_id', null);
                    }
                }
            ])->paginate($limit);

        return response()->json(["data" => $listings]);
    });

    Route::get('/rooms/{id}', function ($id) {
        $yearNow = Carbon::now()->year;
        $monthNow = Carbon::now()->month;
        $user = auth('sanctum')->user();

        $room = Listing::whereHas('user', function ($query) {
            $query->where('verify_account', 1);
        })
            ->with([
                'orders' =>  function ($query) use ($yearNow, $monthNow) {
                    $query->whereYear('check_in', '>=', $yearNow)
                        ->whereMonth('check_in', '>=', $monthNow)
                        ->select('check_in', 'check_out', 'listing_id');
                }
            ])
            ->with('category')
            ->with('amenities')
            ->with('images:id,image,listing_id')
            ->with('address')
            ->with('privacy')
            ->with('user')
            ->with([
                'wishlist' =>  function ($query) use ($user) {
                    if ($user) {
                        $query->where('user_id', $user->id);
                    } else {
                        $query->where('user_id', null);
                    }
                }
            ])
            ->find($id);
        return response()->json(["data" => $room]);
    });

    Route::get('/user/rooms/{id}', function ($id) {
        $user = auth('sanctum')->user();
        $yearNow = Carbon::now()->year;
        $monthNow = Carbon::now()->month;
        if ($user) {
            $room = Listing::where('id', $id)
                ->where('host_id', $user->id)
                ->with([
                    'orders' =>  function ($query) use ($yearNow, $monthNow) {
                        $query->whereYear('check_in', '>=', $yearNow)
                            ->whereMonth('check_in', '>=', $monthNow)
                            ->select('check_in', 'check_out', 'listing_id');
                    }
                ])
                ->with('category')
                ->with('amenities')
                ->with('images:id,image,listing_id')
                ->with('address')
                ->with('privacy')
                ->first();
            return response()->json(["data" => $room]);
        }
        return response()->json(["data" => null]);
    });

    Route::get('/user/rooms', function () {
        $user = auth('sanctum')->user();
        $yearNow = Carbon::now()->year;
        $monthNow = Carbon::now()->month;
        if ($user) {
            $listings = Listing::where('host_id', $user->id)
                ->with([
                    'orders' =>  function ($query) use ($yearNow, $monthNow) {
                        $query->whereYear('check_in', '>=', $yearNow)
                            ->whereMonth('check_in', '>=', $monthNow)
                            ->select('check_in', 'check_out', 'listing_id');
                    }
                ])
                ->with('category')
                ->with('amenities')
                ->with('images:id,image,listing_id')
                ->with('address')
                ->with('privacy')
                ->with('user')
                ->orderByDesc('id')
                ->paginate(request()->limit);
            return response()->json(["data" => $listings]);
        }
        return response()->json(["data" => null]);
    });

    Route::post('/room/create', function () {
        $room = Listing::create([
            'host_id' => request()->host_id,
        ]);
        return response()->json(["data" => $room]);
    });

    Route::post('/room/update', function () {
        $room = Listing::find(request()->id);
        if (isset(request()->host_name)) {
            $room->host_name = request()->host_name;
            $room->save();
        }
        if (request()->category_id) {
            $room->category_id = request()->category_id;
            $room->save();
        }
        if (request()->privacy_id) {
            $room->privacy_id = request()->privacy_id;
            $room->save();
        }
        if (request()->amenities) {
            $room->amenities()->sync(request()->amenities);
        }
        if (request()->latitude && request()->longitude) {
            $room->latitude = request()->latitude;
            $room->longitude = request()->longitude;
            $room->save();
        }
        if (request()->adult && request()->child && request()->bedroom && request()->bathroom) {
            $room->adult = request()->adult;
            $room->child = request()->child;
            $room->bedroom = request()->bedroom;
            $room->bathroom = request()->bathroom;
            $room->save();
        }

        if (request()->hasFile('files')) {
            $listingImages = ListingImage::where('listing_id', request()->id)->get();
            if ($listingImages) {
                foreach ($listingImages as $item) {
                    if ($item['public_id_image'] !== null) {
                        (new UploadApi())->destroy($item['public_id_image']);
                    }
                    ListingImage::destroy($item['id']);
                }
            }

            foreach ($_FILES['files']['tmp_name'] as $image) {
                $detailListing = (new UploadApi())->upload($image, [
                    'folder' => 'ktravel/listings',
                    'format' => 'jpg',
                    'quality' => '80',
                ]);
                $data['image'] =  $detailListing['secure_url'];
                $data['listing_id'] = request()->id;
                $data['public_id_image'] = $detailListing['public_id'];
                ListingImage::create($data);
            }
        }
        if (request()->name) {
            $room->name = request()->name;
            $room->save();
        }
        if (request()->description) {
            $room->description = request()->description;
            $room->save();
        }
        if (request()->price) {
            $room->price = request()->price;
            $room->save();
        }
        if (isset(request()->weekly_discount)) {
            $room->weekly_discount = request()->weekly_discount;
            $room->save();
        }
        if (isset(request()->monthly_discount)) {
            $room->monthly_discount = request()->monthly_discount;
            $room->save();
        }
        if (isset(request()->new_discount)) {
            $room->new_discount = request()->new_discount;
            $room->save();
        }
        if (request()->country && request()->street && request()->state && request()->city) {
            $address = Address::updateOrCreate(
                ['listing_id' =>  request()->id],
                [
                    'city' => request()->city,
                    'country' => request()->country,
                    'street' => request()->street,
                    'state' => request()->state
                ]
            );
        }
        if (isset(request()->status)) {
            $room->status = request()->status;
            $room->save();
        }
        return response()->json(["data" => $room]);
    });

    Route::post('/room/address/update', function () {
        $address = Address::updateOrCreate(
            ['listing_id' =>  request()->id],
            [
                'city' => request()->city,
                'country' => request()->country,
                'street' => request()->street,
                'state' => request()->state
            ]
        );
        return response()->json(["data" => $address]);
    });

    Route::get('/categories', function () {
        return response()->json(["data" => Category::all()]);
    });

    Route::get('/amenities', function () {
        return response()->json(["data" => Amenity::all()]);
    });

    Route::get('/privacy', function () {
        return response()->json(["data" => Privacy::all()]);
    });

    Route::get('/reservation/list', function () {
        $user = auth('sanctum')->user();
        if ($user && request()->person === 'me') {
            $orders = Order::where('user_id', $user->id)
                ->with('listing:id,name')
                ->orderBy('id', 'desc')
                ->paginate(request()->limit);
            foreach ($orders as $order) {
                $order['total'] = $order->price * $order->nights;
            }
        } else {
            $orders = Order::where('host_id', $user->id)
                ->with('listing:id,name')
                ->orderBy('id', 'desc')
                ->paginate(request()->limit);
            foreach ($orders as $order) {
                $order['total'] = $order->price * $order->nights;
            }
        }
        return response()->json(["data" => $orders]);
    });

    Route::post('/wishlist/add', function () {
        $user = auth('sanctum')->user();
        if ($user) {
            try {
                $wishlist = Wishlist::create(['user_id' => $user->id, 'listing_id' => request()->listing_id]);
                $statusCode = ["statusCode" => "OK", 'listing_id' => request()->listing_id];
                $response = ['addWishlistItem' => $statusCode];
            } catch (\Exception $e) {
                $wishlist = Wishlist::where('listing_id', request()->listing_id)->delete();
                $statusCode = ["statusCode" => "OK", 'listing_id' => request()->listing_id];
                $response = ['removeWishlistItem' => $statusCode];
            }
        } else {
            $response = ['auth' => 'nologgin'];
        }
        return response()->json(["data" => $response]);
    });

    Route::get('wishlist', function () {
        $user = auth('sanctum')->user();
        $limit = request()->limit;
        $yearNow = Carbon::now()->year;
        $monthNow = Carbon::now()->month;

        if ($user) {
            $listings = Listing::where('status', 'confirm')
                ->whereHas('user', function ($query) {
                    $query->where('verify_account', 1);
                })->whereHas('wishlist', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })->with([
                    'orders' =>  function ($query) use ($yearNow, $monthNow) {
                        $query->whereYear('check_in', '>=', $yearNow)
                            ->whereMonth('check_in', '>=', $monthNow)
                            ->select('check_in', 'check_out', 'listing_id');
                    }
                ])
                ->with('wishlist')
                ->with('images:id,image,listing_id')
                ->with('address')
                ->where('status', 'confirm')
                ->paginate($limit);
            return response()->json(["data" => $listings]);
        }
        return response()->json(["data" => null]);
    });

    Route::get('/search', function () {
        $user = auth('sanctum')->user();
        $place = request()->place;
        $checkIn = request()->check_in ? Carbon::createFromFormat('d/m/Y', request()->check_in)->format('Y-m-d') : now()->format('Y-m-d');
        $checkOut = request()->check_out ? Carbon::createFromFormat('d/m/Y', request()->check_in)->format('Y-m-d') : now()->format('Y-m-d');
        $adult = request()->adult;
        $child = request()->child;
        $limit = request()->limit;
        $listings = Listing::with('orders')
            ->where('status', 'confirm')
            ->whereHas('user', function ($query) {
                $query->where('verify_account', 1);
            })
            ->whereHas('address', function ($query) use ($place) {
                $query->where('country', 'like', '%' . $place . '%')
                    ->orWhere('city', 'like', '%' . $place . '%')
                    ->orWhere('street', 'like', '%' . $place . '%');
            })->where('adult', '>=', $adult)->where('child', '>=', $child)
            ->with('address')
            ->with('images')
            ->with([
                'wishlist' =>  function ($query) use ($user) {
                    if ($user) {
                        $query->where('user_id', $user->id);
                    } else {
                        $query->where('user_id', null);
                    }
                }
            ])
            ->paginate($limit);
        return response(['data' => $listings]);
    });

    Route::get('/broadcast/message/{id}', function ($id) {
        $user = auth('sanctum')->user();

        $message = Message::with([
            'messageContents' =>  function ($query) {
                $query->with('user:id,name,avatar');
            }
        ])
            ->with('userFrom')
            ->with('userTo')
            ->whereHas('userFrom', function ($query) use ($user) {
                $query->where('user1_id', $user->id)->orWhere('user2_id', $user->id);
            })
            ->find($id);
        if ($message) {
            return response()->json(["data" => $message], 200);
        } else {
            return response()->json(["error" => 'Message not found'], 401);
        }
    });

    Route::get('/broadcast/user/message/list', function () {
        $user = auth('sanctum')->user();
        if ($user) {
            $message = Message::where('user1_id', $user->id)->orWhere('user2_id', $user->id)
                ->with('userFrom')
                ->with('userTo')
                ->with([
                    'listing' =>  function ($query) {
                        $query->with('images');
                    }
                ])->orderBy('id', 'desc')->paginate(10);
            return response()->json(["data" => $message]);
        }
        return response()->json(["data" => null]);
    });

    Route::post('/message/add', function () {
        $user = auth('sanctum')->user();
        $roomId = request()->room_id;
        $roomHostId = request()->room_host_id;

        $listing = Listing::whereHas('messages', function ($query) use ($user, $roomHostId) {
            $query->where('user1_id',  $user->id)->where('user2_id',  $roomHostId);
        })->find($roomId);

        if ($user) {
            if (!$listing) {
                $message = Message::create([
                    'id' => $user->id . $roomHostId . $roomId,
                    'user1_id' => $user->id,
                    'user2_id' => $roomHostId,
                    'listing_id' => $roomId
                ]);
                return response()->json(["data" => $message]);
            } else {
                $response = ["statusCode" => "message is has already been created", 'id' => $user->id . $roomHostId . $roomId];
                return response()->json(["data" => $response]);
            }
        }
    });

    Route::post('/broadcast', function () {
        $messId = request()->message_id;
        $userId = request()->user_id;
        $userName = request()->user_name;
        $avatar = request()->avatar;
        $message = request()->message;
        $messageTime = request()->message_time;
        $channel = request()->channel;

        event(new PusherBroadcast($userId, $userName, $avatar, $message, $messageTime, $channel));

        $messageContent = MessageContent::create([
            'message_id' => $messId,
            'user_id' => $userId,
            'content' => $message
        ]);
        return response()->json(["data" => $messageContent]);
    });

    Route::post('/checkout/paypal', [PaypalController::class, 'payment'])->name('checkout.paypal');
    Route::get('/paypal/success', [PaypalController::class, 'success'])->name('checkout.paypal.success');;
    Route::get('/paypal/cancel', [PaypalController::class, 'cancel'])->name('checkout.paypal.cancel');

    Route::post('/social/login', function () {
        $user = User::where('provider_id', request()->provider_id)->orWhere('email', request()->email)->first();

        if (!isset($user)) {
            $user = User::create([
                'name' => request()->name,
                'email' => request()->email ? request()->email : "",
                'password' =>  Hash::make(request()->provider_id),
                'provider_id' => request()->provider_id,
                'provider_type' => request()->provider
            ]);
        }

        if (isset($user) && $user->provider_id === null) {
            $user = User::find($user->id);
            $user->provider_id = request()->provider_id;
            $user->provider_type = request()->provider;
            $user->save();
        }

        if ($user) {
            $data['token'] = $user->createToken('accessToken')->plainTextToken;
            return response()->json(['data' => $data], 200);
        } else {
            return response()->json(['error' => 'Unauthorised'], 401);
        }
    })->name('api-social-login');

    Route::get('/test', function () {
        (new UploadApi())->destroy('');
    });

    Route::post('/login', function (Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails())
            return response()->json(['code' => 400, 'message' => $validator->errors()], 200);
        // Attempt to authenticate
        if (Auth::attempt(
            [
                'email' => request('email'),
                'password' => request('password'),
            ]
        )) {
            $user = Auth::user();
            $data['token'] = $user->createToken('accessToken')->plainTextToken;
            return response()->json(['data' => $data], 200);
        } else {
            return response()->json(['error' => 'Unauthorised'], 401);
        }
    })->name('api.login');

    Route::get('/profile', function () {
        $user = auth('sanctum')->user();
        if (!$user) {
            return response()->json(['data' => null], 200);
        }
        return response()->json(['data' => $user], 200);
    });

    Route::post('/logout', function () {
        $user = auth('sanctum')->user();
        $user->tokens()->delete();
        $data['message'] = 'Logout Success';
        return response()->json(['data' => $data], 200);
    });

    Route::post('/register', function (Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:users,email',
            'phone' => ['required', 'regex:/^(\+84|84|0)[0-9]{9}$/'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'email.unique' => 'Email đã tồn tại',
            'password.confirmed' => 'Mật khẩu nhập lại không chính xác'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role_id' => null,
            'password' => Hash::make($request->password),
        ]);
        return response()->json(['message' => 'Success'], 200);
    })->name('api.register');

    Route::post('/profile/update', function () {
        $user = auth('sanctum')->user();
        $name = request()->name;
        $email = request()->email;
        $phone = request()->phone;
        $password = request()->password;
        $password_current = request()->password_current;
        $avatar = request()->hasFile('avatar');

        if (!$user) {
            return response()->json(['data' => null], 200);
        } else {
            if ($name) {
                $user->name = $name;
                $user->save();
            } else if ($email) {
                $user->email = $email;
                $user->save();
            } else if ($phone) {
                $user->phone = $phone;
                $user->save();
            } else if ($avatar) {
                $userAvatar = (new UploadApi())->upload($_FILES['avatar']['tmp_name'], [
                    'folder' => 'ktravel/account',
                    'quality' => '80',
                ]);

                if ($user->public_id_avatar !== null) {
                    (new UploadApi())->destroy($user->public_id_avatar);
                }

                $user->avatar = $userAvatar['secure_url'];
                $user->public_id_avatar = $userAvatar['public_id'];
                $user->save();
            } else {
                $check = Hash::check($password_current, $user->password);
                request()->validate([
                    'password' => ['required', 'confirmed', Rules\Password::defaults()],
                ]);

                if ($check) {
                    $user->password = Hash::make($password);
                    $user->save();
                } else {
                    return response()->json(['error' => 'Mật khẩu hiện tại không chính xác'], 401);
                }
            }
        }

        $statusCode = ["statusCode" => "OK"];
        $response = ['updateUser' => $statusCode];
        return response()->json(['data' => [$user, $response]], 200);
    });

    Route::post('/account/verify', function () {
        $user = auth('sanctum')->user();
        //request()->hasFile('front_card'), request()->hasFile('back_card'), request()->hasFile('selfile')

        if (request()->hasFile('front_card') && request()->hasFile('back_card')) {
            $frontCard = (new UploadApi())->upload($_FILES['front_card']['tmp_name'], [
                'folder' => 'ktravel/account',
                'quality' => '80',
            ]);

            $backCard = (new UploadApi())->upload($_FILES['back_card']['tmp_name'], [
                'folder' => 'ktravel/account',
                'quality' => '80',
            ]);

            if ($user->public_id_front_card !== null) {
                (new UploadApi())->destroy($user->public_id_front_card);
            }

            if ($user->public_id_back_card !== null) {
                (new UploadApi())->destroy($user->public_id_back_card);
            }

            $user->front_card = $frontCard['secure_url'];
            $user->back_card = $backCard['secure_url'];
            $user->public_id_front_card = $frontCard['public_id'];
            $user->public_id_back_card = $backCard['public_id'];
            $user->save();

            $statusCode = ["statusCode" => "OK", 'user_id' => $user->id];
            $response = ['updateUser' => $statusCode];
            return response()->json(["data" => $response]);
        }

        if (request()->selfile) {
            $selfile = (new UploadApi())->upload(request()->selfile, [
                'folder' => 'ktravel/account',
                'quality' => '80',
            ]);
            if ($user->public_id_selfile !== null) {
                (new UploadApi())->destroy($user->public_id_selfile);
            }
            $user->selfile = $selfile['secure_url'];
            $user->public_id_selfile = $selfile['public_id'];
            $user->save();

            $statusCode = ["statusCode" => "OK", 'user_id' => $user->id];
            $response = ['updateUser' => $statusCode];
            return response()->json(["data" => $response]);
        }

        $statusCode = ["statusCode" => "Failed", 'user_id' => $user->id];
        $response = ['updateUser' => $statusCode];
        return response()->json(["data" => $response]);
    });

    Route::post('/forgot-password', function () {
        request()->validate([
            'email' => 'required|email',
        ]);

        $status = Password::sendResetLink(
            request()->only('email')
        );

        if ($status == Password::RESET_LINK_SENT) {
            return back()->with('status', __($status));
        }

        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
    });

    Route::post('/reset-password', function (Request $request) {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );
        if ($status == Password::PASSWORD_RESET) {
            return true;
        }

        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
    })->middleware('guest')->name('password.update');
});
