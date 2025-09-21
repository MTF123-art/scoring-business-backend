<?php

namespace App\Services;

use App\Models\SocialAccount;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Log\LogManager;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class FacebookService
{
   protected string $baseUrl = 'https://graph.facebook.com/v23.0';
   protected $http;
   protected $log;

   public function __construct(HttpFactory $http, LogManager $log)
   {
      $this->http = $http;
      $this->log = $log;
   }

   public function connectAccount(int $userId, SocialiteUser $facebookUser)
   {
      $shortLivedToken = $facebookUser->token;
      [$longLivedToken, $expiresAt] = $this->exchangeForLongLivedToken($shortLivedToken);
      return SocialAccount::updateOrCreate(
         [
            'provider' => 'facebook',
            'provider_id' => $facebookUser->getId(),
         ],
         [
            'user_id' => $userId,
            'name' => $facebookUser->getName() ?? $facebookUser->getNickname(),
            'avatar' => $facebookUser->getAvatar(),
            'access_token' => $longLivedToken,
            'expires_at' => $expiresAt,
         ],
      );
   }

   protected function exchangeForLongLivedToken(string $shortLivedToken)
   {
      $response = $this->http->get($this->baseUrl . '/oauth/access_token', [
         'grant_type' => 'fb_exchange_token',
         'client_id' => config('services.facebook.client_id'),
         'client_secret' => config('services.facebook.client_secret'),
         'fb_exchange_token' => $shortLivedToken,
      ]);
      if ($response->failed()) {
         $this->log->error('Facebook token exchange failed', [
            'response_status' => $response->status(),
            'response_body' => $response->body(),
         ]);
         throw new \Exception('Failed to exchange token: ' . $response->body());
      }
      $data = $response->json();
      $token = $data['access_token'];
      $expiresIn = $data['expires_in'] ?? 60 * 60 * 24 * 60; // fallback 60 hari
      $expiresAt = now()->addSeconds($expiresIn);
      return [$token, $expiresAt];
   }

   public function refreshAccessToken(string $longLivedToken)
   {
      $response = $this->http->get($this->baseUrl . '/oauth/access_token', [
         'grant_type' => 'fb_exchange_token',
         'client_id' => config('services.facebook.client_id'),
         'client_secret' => config('services.facebook.client_secret'),
         'fb_exchange_token' => $longLivedToken,
      ]);
      if ($response->failed()) {
         $this->log->error('Facebook token refresh failed', [
            'response_status' => $response->status(),
            'response_body' => $response->body(),
         ]);
         throw new \Exception('Failed to refresh token: ' . $response->body());
      }
      $data = $response->json();
      $token = $data['access_token'];
      $expiresIn = $data['expires_in'] ?? 60 * 60 * 24 * 60;
      $expiresAt = now()->addSeconds($expiresIn);
      return [$token, $expiresAt];
   }
}
