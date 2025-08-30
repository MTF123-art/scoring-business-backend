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
    /**
     * Generate redirect URL ke Instagram dengan state custom
     */
    public function redirectToInstagram(Request $request): JsonResponse
    {
        // generate random state
        $state = Str::random(32);

        // simpan ke cache biar bisa diverifikasi di callback (10 menit)
        Cache::put("oauth_state:{$state}", $request->user()->id, now()->addMinutes(10));

        // generate URL redirect
        $redirectUrl = Socialite::driver('instagram')
            ->stateless() // tidak pakai session Laravel
            ->scopes(['instagram_business_basic', 'instagram_business_manage_insights'])
            ->with(['state' => $state])
            ->redirect()
            ->getTargetUrl();

        return response()->json([
            'url' => $redirectUrl,
        ]);
    }

    public function handleCallback(Request $request): JsonResponse
    {
        try {
            $state = $request->query('state');
            $code = $request->query('code');

            // cek state valid
            $userId = Cache::pull("oauth_state:{$state}");
            if (!$userId) {
                return response()->json(['error' => 'Invalid or expired state'], 400);
            }

            // ambil data user IG
            $instagramUser = Socialite::driver('instagram')->stateless()->user();

            // simpan ke DB via service
            $this->instagramService->connectAccount($userId, $instagramUser);

            return response()->json([
                'message' => 'Instagram account connected successfully',
                'data' => [
                    'id' => $instagramUser->getId(),
                    'name' => $instagramUser->getName() ?? $instagramUser->getNickname(),
                    'avatar' => $instagramUser->getAvatar(),
                    'account_type' => $instagramUser->account_type,
                    'token' => $instagramUser->token,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
