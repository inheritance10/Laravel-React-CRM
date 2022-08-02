<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Testing\Fluent\Concerns\Has;
use phpseclib3\Crypt\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
           'name' => 'required|string',
           'email' => 'required|string|email|unique|users',
            'password' => 'required|string|confirmed'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);


        $credentials = ['email' => $request->email,'password' => $request->password];

        if(!Auth::attempt($credentials)){
            return response()->json([
                'message' => 'giriş yapılamadı'
            ],401);
        }

        $user = $request->user();

        $tokenResult = $user->createToken('Personal Access');
        $token = $tokenResult->token;
        if($request->remember_me) {
            $token->expires_at = Carbon::now()->addWeek(1);
        }
        $token->save();


        return response()->json([
            'success' => true,
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse($tokenResult->token->expires_at)->toDateTimeString()

        ],201);
    }



    public function login(Request $request){
        $request->validate([
           'email' => 'require|string|email',
            'password' => 'require|string',
            'remember_me' => 'boolean'
        ]);


        $credentials = ['email' => $request->email,'password' => $request->password];

        if(!Auth::attempt($credentials)){
            return response()->json([
                  'message' => 'Bilgiler Hatalı Kontrol Ediniz'
            ],401);
        }

        $user = $request->user();


        $tokenResult = $user->createToken('Personal Access Token');

        $token = $tokenResult->token;

        if($request->remeber_me){
            $token->expires_at = Carbon::now()->addWeeks(1);
        }

        $token->save();

        return response()->json([
            'success' => true,
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse($tokenResult->token->expires_at)->toDateTimeString()

        ],201);

    }


    public function logout(Request $request){
        $request->user()->token()->revoke();
        return response()->json([
              'message' => 'Çıkış yapıldı'
        ]);
    }

    public function user(Request $request){
        return response()->json($request->user());
    }
}
