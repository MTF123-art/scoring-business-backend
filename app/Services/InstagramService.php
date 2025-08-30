<?php

namespace App\Services;

use App\Models\SocialAccount;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;

class InstagramService
{
    public function connectAccount(int $userId, SocialiteUser $instagramUser): SocialAccount
    {
        $shortLivedToken = $instagramUser->token;

        [$longLivedToken, $expiresAt] = $this->exchangeForLongLivedToken($shortLivedToken);

        return SocialAccount::updateOrCreate(
            [
                'provider' => 'instagram',
                'provider_id' => $instagramUser->getId(),
            ],
            [
                'user_id' => $userId,
                'name' => $instagramUser->getName() ?? $instagramUser->getNickname(),
                'avatar' => $instagramUser->getAvatar(),
                'access_token' => $longLivedToken,
                'expires_at' => $expiresAt,
            ],
        );
    }

    protected function exchangeForLongLivedToken(string $shortLivedToken): array
    {
        $response = Http::get('https://graph.instagram.com/access_token', [
            'grant_type' => 'ig_exchange_token',
            'client_secret' => config('services.instagram.client_secret'),
            'access_token' => $shortLivedToken,
        ]);

        if ($response->failed()) {
            throw new \Exception('Failed to exchange token: ' . $response->body());
        }

        $data = $response->json();

        $token = $data['access_token'];
        $expiresIn = $data['expires_in'];
        $expiresAt = now()->addSeconds($expiresIn);

        return [$token, $expiresAt];
    }
}
