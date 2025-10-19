<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Auth\LoginRequest;
use App\Http\Requests\API\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User register successfully',
            'token' => $user->createToken('authToken')->plainTextToken,
            'user' => $user,
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = request(['email', 'password']);
        if(!Auth::attempt($credentials)){
            return response()->json([
                'message' => 'Unauthorized'
            ]);
        }else{
            return response()->json([
                'message' => 'Successfully logged in',
                'token' => Auth::user()->createToken('Token')->plainTextToken
            ]);
        }
    }

    public function logout(Request $request)
    {
        auth()->user()->tokens()->delete();

        auth()->logout();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }
}
