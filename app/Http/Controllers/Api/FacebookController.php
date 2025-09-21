<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FacebookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Laravel\Socialite\Facades\Socialite;

class FacebookController extends Controller
{
    protected FacebookService $facebookService;

    public function __construct(FacebookService $facebookService)
    {
        $this->facebookService = $facebookService;
    }

    public function redirectToFacebook(Request $request): JsonResponse
    {
        try {
            $state = Str::random(32);
            Cache::put("oauth_state:{$state}", $request->user()->id, now()->addMinutes(10));
            $redirectUrl = Socialite::driver('facebook')
                ->stateless()
                ->scopes(['pages_show_list', 'pages_read_engagement', 'pages_read_user_content'])
                ->with(['state' => $state])
                ->redirect()
                ->getTargetUrl();
            return api_success(
                [
                    'url' => $redirectUrl,
                ],
                'url login facebook berhasil dibuat',
            );
        } catch (\Exception $e) {
            return api_error('gagal membuat url login facebook', 500, $e->getMessage());
        }
    }

    public function handleCallback(Request $request): JsonResponse
    {
        try {
            $state = $request->query('state');
            $userId = Cache::pull("oauth_state:{$state}");
            if (!$userId) {
                return api_error('state tidak valid atau kadaluarsa');
            }
            try {
                $facebookUser = Socialite::driver('facebook')->stateless()->user();
            } catch (\Exception $e) {
                return api_error('gagal mengambil data user facebook', 400, $e->getMessage());
            }
            try {
                $this->facebookService->connectAccount($userId, $facebookUser);
            } catch (\Exception $e) {
                return api_error('gagal menyimpan akun facebook', 400, $e->getMessage());
            }
            return api_success(
                [
                    'user' => [
                        'id' => $facebookUser->getId(),
                        'name' => $facebookUser->getName() ?? $facebookUser->getNickname(),
                        'avatar' => $facebookUser->getAvatar(),
                    ],
                ],
                'berhasil terhubung dengan akun facebook',
            );
        } catch (\Exception $e) {
            return api_error('gagal terhubung dengan akun facebook', 500, $e->getMessage());
        }
    }
}