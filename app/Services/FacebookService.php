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
      $expiresIn = $data['expires_in'] ?? 60 * 60 * 24 * 60;
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

   public function getMetrics(SocialAccount $account)
   {
      $accessToken = $account->access_token;
      $pageId = $account->provider_id;

      $profileResponse = $this->http->get("{$this->baseUrl}/{$pageId}", [
         'fields' => 'fan_count,posts.limit(1).summary(true)',
         'access_token' => $accessToken,
      ]);
      if ($profileResponse->failed()) {
         $this->log->error('Gagal mengambil profil Facebook', [
            'response_status' => $profileResponse->status(),
            'response_body' => $profileResponse->body(),
         ]);
         throw new \Exception('Gagal mengambil data profil Facebook');
      }
      $profile = $profileResponse->json();
      $followers = $profile['fan_count'] ?? 0;
      $mediaCount = $profile['posts']['summary']['total_count'] ?? 0;

      $mediaResponse = $this->http->get("{$this->baseUrl}/{$pageId}/posts", [
         'fields' => 'id,insights.metric(post_impressions,post_engaged_users),likes.summary(true),comments.summary(true)',
         'limit' => 10,
         'access_token' => $accessToken,
      ]);
      if ($mediaResponse->failed()) {
         $this->log->error('Gagal mengambil post Facebook', [
            'response_status' => $mediaResponse->status(),
            'response_body' => $mediaResponse->body(),
         ]);
         throw new \Exception('Gagal mengambil data post Facebook');
      }
      $posts = $mediaResponse->json()['data'] ?? [];

      $totalLikes = 0;
      $totalComments = 0;
      $totalReach = 0;
      $totalEngagement = 0;
      $postCount = count($posts);

      foreach ($posts as $post) {
         $likes = $post['likes']['summary']['total_count'] ?? 0;
         $comments = $post['comments']['summary']['total_count'] ?? 0;
         $reach = 0;
         $engaged = 0;

         if (isset($post['insights']['data'])) {
            foreach ($post['insights']['data'] as $insight) {
               if ($insight['name'] === 'post_impressions') {
                  $reach = $insight['values'][0]['value'] ?? 0;
               }
               if ($insight['name'] === 'post_engaged_users') {
                  $engaged = $insight['values'][0]['value'] ?? 0;
               }
            }
         }

         $totalLikes += $likes;
         $totalComments += $comments;
         $totalReach += $reach;
         $totalEngagement += $engaged;
      }

      // Engagement Rate (menggunakan engaged users)
      $engagementRate = $followers > 0 ? ($totalEngagement / $followers) * 100 : 0;
      // Reach Ratio
      $reachRatio = $followers > 0 ? $totalReach / $followers : 0;
      // Engagement per Post
      $engagementPerPost = $postCount > 0 ? $totalEngagement / $postCount : 0;

      return [
         'followers' => $followers,
         'media_count' => $mediaCount,
         'total_likes' => $totalLikes,
         'total_comments' => $totalComments,
         'total_reach' => $totalReach,
         'engagement_rate' => round($engagementRate, 2),
         'reach_ratio' => round($reachRatio, 2),
         'engagement_per_post' => round($engagementPerPost, 2),
         'post_count' => $postCount,
      ];
   }
}
