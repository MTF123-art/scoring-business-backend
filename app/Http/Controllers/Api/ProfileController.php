<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function getProfile(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return api_error('unauthenticated', 401);
            }
            $profile = $user->toArray();
            $profile['avatar_url'] = user_avatar_url($user);
            return api_success($profile, 'profil berhasil diambil');
        } catch (\Exception $e) {
            return api_error('gagal mengambil profil', 500, $e->getMessage());
        }
    }

    public function updateProfile(Request $request)
    {
        try {
            Log::info('updateProfile called', [
                'user_id' => optional($request->user())->id,
                'has_name' => $request->has('name'),
                'has_email' => $request->has('email'),
                'has_avatar' => $request->hasFile('avatar'),
                'content_type' => $request->header('Content-Type')
            ]);
            $user = $request->user();
            if (!$user) {
                return api_error('unauthenticated', 401);
            }

            $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
                'avatar' => 'sometimes|file|image|max:2048',
            ]);

            if ($request->filled('name')) {
                Log::info('Updating name');
                $user->name = $request->input('name');
            }
            if ($request->filled('email')) {
                Log::info('Updating email');
                $user->email = $request->input('email');
            }
            if ($request->hasFile('avatar')) {
                Log::info('Avatar file detected, processing');
                if ($user->avatar_url && Storage::disk('private')->exists($user->avatar_url)) {
                    Log::info('Deleting old avatar', ['path' => $user->avatar_url]);
                    Storage::disk('private')->delete($user->avatar_url);
                }
                $file = $request->file('avatar');
                $filename = 'user_' . $user->id . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('avatars', $filename, 'private');
                Log::info('Stored new avatar', ['path' => $path]);
                $user->avatar_url = $path;
            }

            $user->save();
            Log::info('User profile saved');
            $user = $user->fresh();

            $profile = $user->toArray();
            $profile['avatar_url'] = user_avatar_url($user);
            return api_success($profile, 'profil berhasil diperbarui');
        } catch (\Exception $e) {
            Log::error('updateProfile error', ['message' => $e->getMessage()]);
            return api_error('gagal memperbarui profil', 500, $e->getMessage());
        }
    }

    public function changePassword(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return api_error('unauthenticated', 401);
            }

            $request->validate([
                'current_password' => 'required',
                'new_password' => 'required|string|min:6|confirmed',
            ]);

            if (!Hash::check($request->current_password, $user->password)) {
                return api_error('password saat ini salah');
            }

            $user->password = Hash::make($request->new_password);
            $user->save();

            return api_success(null, 'password berhasil diubah');
        } catch (\Exception $e) {
            return api_error('gagal mengubah password', 500, $e->getMessage());
        }
    }

    public function getAvatarById($id)
    {
        $user = User::find($id);
        if (!$user || !$user->avatar_url) {
            abort(404);
        }
        $path = storage_path('app/private/' . $user->avatar_url);
        if (!file_exists($path)) {
            abort(404);
        }
        return response()->file($path);
    }
}
