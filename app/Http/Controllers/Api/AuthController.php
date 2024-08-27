<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public  function register(Request $request)
    {
       $data =  $request->validate([
            "name" => "required",
            "email" => "required|email|unique:users",
            "password" => "required|confirmed",
            'role' => 'required|in:admin,vendeur,client',
       ]);
       $data['password'] = Hash::make($request->input('password'));
       $user = User::create($data);

       return response()->json([

           'statut' => 201,
           'data' => $user,
           "token" =>  null

       ]);

    }


    public function login(Request $request)
    {
        $data =  $request->validate([
            "email" => "required|email|",
            "password" => "required"
        ]);

        $token = JWTAuth::attempt($data);

        if(!empty($token))
        {
            return response()->json([
                'statut' => 200,
                'data'=> auth()->user(),
                "token" =>  $token
            ]);

        }else{
            return response()->json([
                "statut" => false,
                "token" =>  null
            ]);
        }
    }



    public function logout()
    {
        auth()->logout();
        return response()->json([
            'statut' => true,
            "message" =>  "utilisateur s'est deconnecte !"
        ]);
    }

    public  function refresh()
    {
        $newToken = auth()->refresh();
        return response()->json([
            'statut' => true,
            "token" =>  $newToken
        ]);
    }
}
