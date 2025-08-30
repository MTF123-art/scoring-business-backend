<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\InstagramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Laravel\Socialite\Facades\Socialite;

class InstagramController extends Controller
{
    protected InstagramService $instagramService;

    public function __construct(InstagramService $instagramService)
    {
        $this->instagramService = $instagramService;
    }

    public function redirectToInstagram(Request $request): JsonResponse
    {
        $state = Str::random(32);

        Cache::put("oauth_state:{$state}", $request->user()->id, now()->addMinutes(10));

        $redirectUrl = Socialite::driver('instagram')
            ->stateless()
            ->scopes(['instagram_business_basic', 'instagram_business_manage_insights'])
            ->with(['state' => $state])
            ->redirect()
            ->getTargetUrl();

        return api_success(
            [
                'url' => $redirectUrl,
            ],
            'url login instagram berhasil dibuat',
        );
    }

    public function handleCallback(Request $request): JsonResponse
    {
        try {
            $state = $request->query('state');

            $userId = Cache::pull("oauth_state:{$state}");
            if (!$userId) {
                return api_error('state tidak valid atau kadaluarsa');
            }

            $instagramUser = Socialite::driver('instagram')->stateless()->user();

            $this->instagramService->connectAccount($userId, $instagramUser);

            return api_success(
                [
                    'user' => [
                        'id' => $instagramUser->getId(),
                        'name' => $instagramUser->getName() ?? $instagramUser->getNickname(),
                        'avatar' => $instagramUser->getAvatar(),
                        'account_type' => $instagramUser->account_type,
                    ],
                ],
                'berhasil terhubung dengan akun instagram',
            );
        } catch (\Exception $e) {
            return api_error('gagal terhubung dengan akun instagram', 400, $e->getMessage());
        }
    }
}
