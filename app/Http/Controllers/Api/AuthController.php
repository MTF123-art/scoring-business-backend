<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

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

            return api_success(['user' => $user], 'pendaftaran berhasil', 201);
        } catch (ValidationException $e) {
            return api_error('Validasi gagal', 422, $e->errors());
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
                return api_error('email atau password salah', 401);
            }

            $token = $user->createToken('mobile-app-token')->plainTextToken;

            return api_success([
                'user' => $user,
                'sanctum_token' => $token,
            ], 'login berhasil');
        } catch (ValidationException $e) {
            return api_error('Validasi gagal', 422, $e->errors());
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

    public function resetPassword(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return api_error('User dengan email tersebut tidak ditemukan', 404, null);
            }

            $tempPassword = $this->generateTempPassword(8);
            $user->password = Hash::make($tempPassword);
            $user->save();

            Mail::to($user->email)->send(new PasswordResetMail($user->name, $tempPassword));

            return api_success(null, 'Password sementara telah dikirim ke email Anda');
        } catch (ValidationException $e) {
            return api_error('Validasi gagal', 422, $e->errors());
        } catch (\Exception $e) {
            return api_error('Terjadi kesalahan saat mereset password', 500, $e->getMessage());
        }
    }

    private function generateTempPassword($length = 8)
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $password;
    }
}
