<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6|confirmed',
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            return api_success(['user' => $user], 'pendaftaran berhasil');
        } catch (\Exception $e) {
            return api_error('Terjadi kesalahan saat pendaftaran', 500, $e->getMessage());
        }
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return api_error('email atau password salah');
            }

            $token = $user->createToken('mobile-app-token')->plainTextToken;

            return api_success([
                'user' => $user,
                'sanctum_token' => $token,
            ], 'login berhasil');
        } catch (\Exception $e) {
            return api_error('Terjadi kesalahan saat login', 500, $e->getMessage());
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return api_success(null, 'logout berhasil');
        } catch (\Exception $e) {
            return api_error('Terjadi kesalahan saat logout', 500, $e->getMessage());
        }
    }
}
