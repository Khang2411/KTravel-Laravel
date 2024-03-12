<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    //
    function login(Request $request)
    {
        if (Auth::attempt(
            [
                'email' => request('email'),
                'password' => request('password')
            ]
        )) {
            $user = Auth::user();
            $token = $user->createToken('access_token')->accessToken;

            return response()->json(
                [
                    "access_token" => $token
                ],
                200
            );
        } else {
            return response()->json(
                [
                    'error' => 'Unauthorised'
                ],
                401
            );
        }
    }

    function me()
    {
        return response()->json(["user" => Auth::user()], 200);
    }
}
