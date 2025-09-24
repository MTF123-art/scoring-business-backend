<?php

namespace App\Services;

use App\Models\Metric;
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
      $facebookPage = $this->getUserPages($facebookUser->getId(), $longLivedToken);
      return SocialAccount::updateOrCreate(
         [
            'provider' => 'facebook',
            'provider_id' => $facebookPage['0']['id'],
         ],
         [
            'user_id' => $userId,
            'name' => $facebookUser->getName() ?? $facebookUser->getNickname(),
            'avatar' => $facebookUser->getAvatar(),
            'access_token' => $facebookPage['0']['access_token'],
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

   protected function getUserPages(string $userId, string $userAccessToken): array
   {
      $url = $this->baseUrl . "/{$userId}/accounts";
      $response = $this->http->get($url, [
         'access_token' => $userAccessToken,
      ]);
      if ($response->failed()) {
         $this->log->error('Gagal mengambil daftar Facebook Page', [
            'response_status' => $response->status(),
            'response_body' => $response->body(),
         ]);
         throw new \Exception('Gagal mengambil daftar Facebook Page');
      }
      $data = $response->json();
      return $data['data'] ?? [];
   }

   /**
    * Ambil jumlah followers page (followers_count dengan fallback fan_count)
    */
   protected function fetchFollowers(string $pageId, string $accessToken): int
   {
      $resp = $this->http->get("{$this->baseUrl}/{$pageId}", [
         'fields' => 'followers_count',
         'access_token' => $accessToken,
      ]);
      if ($resp->failed()) {
         $this->log->error('Gagal mengambil followers_count Page Facebook', [
            'page_id' => $pageId,
            'response_status' => $resp->status(),
            'response_body' => $resp->body(),
         ]);
         throw new \Exception('Gagal mengambil jumlah follower Facebook');
      }
      $j = $resp->json();
      return (int)($j['followers_count'] ?? 0);
   }

   /**
    * Ambil daftar post beserta ringkasan comments, reactions dan shares
    */
   protected function fetchPostsWithEdgeCounts(string $pageId, string $accessToken, int $limit = 10): array
   {
      $resp = $this->http->get("{$this->baseUrl}/{$pageId}/posts", [
         'fields' => 'id,comments.summary(true).limit(0),reactions.summary(true).limit(0),shares',
         'limit' => $limit,
         'access_token' => $accessToken,
      ]);
      if ($resp->failed()) {
         $this->log->error('Gagal mengambil daftar post Facebook', [
            'page_id' => $pageId,
            'response_status' => $resp->status(),
            'response_body' => $resp->body(),
         ]);
         throw new \Exception('Gagal mengambil daftar post Facebook');
      }
      $data = $resp->json();
      return $data['data'] ?? [];
   }

   /**
    * Ambil reach unique dari post
    */
   protected function fetchPostReachUnique(string $postId, string $accessToken): int
   {
      $resp = $this->http->get("{$this->baseUrl}/{$postId}/insights", [
         'metric' => 'post_impressions_unique',
         'period' => 'lifetime',
         'access_token' => $accessToken,
      ]);
      if ($resp->failed()) {
         $this->log->warning('Gagal mengambil reach post Facebook (post_impressions_unique)', [
            'post_id' => $postId,
            'response_status' => $resp->status(),
            'response_body' => $resp->body(),
         ]);
         return 0;
      }
      $arr = $resp->json()['data'] ?? [];
      foreach ($arr as $item) {
         if (($item['name'] ?? '') === 'post_impressions_unique') {
            return (int)($item['values'][0]['value'] ?? 0);
         }
      }
      return 0;
   }

   /**
    * Ambil daftar reels beserta likes & comments
    */
   protected function fetchReelsWithEdgeCounts(string $pageId, string $accessToken, int $limit = 10): array
   {
      $resp = $this->http->get("{$this->baseUrl}/{$pageId}/video_reels", [
         'fields' => 'id,description,likes.limit(0).summary(true),comments.limit(0).summary(true)',
         'limit' => $limit,
         'access_token' => $accessToken,
      ]);
      if ($resp->failed()) {
         $this->log->warning('Gagal mengambil daftar reels Facebook', [
            'page_id' => $pageId,
            'response_status' => $resp->status(),
            'response_body' => $resp->body(),
         ]);
         return [];
      }
      $data = $resp->json();
      return $data['data'] ?? [];
   }

   /**
    * Ambil reach unique dari reels (video_insights)
    */
   protected function fetchReelReachUnique(string $reelId, string $accessToken): int
   {
      $resp = $this->http->get("{$this->baseUrl}/{$reelId}/video_insights", [
         'metric' => 'post_impressions_unique',
         'access_token' => $accessToken,
      ]);
      if ($resp->failed()) {
         $this->log->warning('Gagal mengambil reach reels Facebook (video_insights)', [
            'reel_id' => $reelId,
            'response_status' => $resp->status(),
            'response_body' => $resp->body(),
         ]);
         return 0;
      }
      $arr = $resp->json()['data'] ?? [];
      foreach ($arr as $item) {
         if (($item['name'] ?? '') === 'post_impressions_unique') {
            return (int)($item['values'][0]['value'] ?? 0);
         }
      }
      return 0;
   }

   /**
    * Agregasi metrik dari posts & reels
    */
   protected function aggregateFromPostsAndReels(array $posts, array $reels, string $accessToken): array
   {
      $totalLikes = 0;
      $totalComments = 0;
      $totalShares = 0;
      $totalReach = 0;

      // Posts
      foreach ($posts as $post) {
         $postId = $post['id'] ?? null;
         if (!$postId) {
            continue;
         }
         $likes = (int)($post['reactions']['summary']['total_count'] ?? 0);
         $comments = (int)($post['comments']['summary']['total_count'] ?? 0);
         $shares = (int)($post['shares']['count'] ?? 0);
         $reach = $this->fetchPostReachUnique($postId, $accessToken);

         $totalLikes += $likes;
         $totalComments += $comments;
         $totalShares += $shares;
         $totalReach += $reach;
      }

      // Reels
      foreach ($reels as $reel) {
         $reelId = $reel['id'] ?? null;
         if (!$reelId) {
            continue;
         }
         $likes = (int)($reel['likes']['summary']['total_count'] ?? 0);
         $comments = (int)($reel['comments']['summary']['total_count'] ?? 0);
         $reach = $this->fetchReelReachUnique($reelId, $accessToken);

         $totalLikes += $likes;
         $totalComments += $comments;
         $totalReach += $reach;
      }

      $postCount = count($posts) + count($reels);
      $totalEngagement = $totalLikes + $totalComments + $totalShares;

      return [
         'total_likes' => $totalLikes,
         'total_comments' => $totalComments,
         'total_shares' => $totalShares,
         'total_reach' => $totalReach,
         'total_engagement' => $totalEngagement,
         'post_count' => $postCount,
      ];
   }

   /**
    * Hitung metrik turunan untuk Page
    */
   protected function computeDerivedMetrics(int $followers, int $totalReach, int $totalEngagement, int $postCount): array
   {
      $engagementRate = $followers > 0 ? ($totalEngagement / $followers) * 100 : 0;
      $reachRatio = $followers > 0 ? $totalReach / $followers : 0;
      $engagementPerPost = $postCount > 0 ? $totalEngagement / $postCount : 0;

      return [
         'engagement_rate' => round($engagementRate, 2),
         'reach_ratio' => round($reachRatio, 2),
         'engagement_per_post' => round($engagementPerPost, 2),
      ];
   }

   public function getMetrics(SocialAccount $account)
   {
      $accessToken = $account->access_token;
      $pageId = $account->provider_id;

      // Followers
      $followers = $this->fetchFollowers($pageId, $accessToken);

      // Posts + edge counts
      $posts = $this->fetchPostsWithEdgeCounts($pageId, $accessToken, 10);

      // Reels + edge counts
      $reels = $this->fetchReelsWithEdgeCounts($pageId, $accessToken, 10);

      // Aggregate
      $agg = $this->aggregateFromPostsAndReels($posts, $reels, $accessToken);

      // Derived
      $derived = $this->computeDerivedMetrics($followers, $agg['total_reach'], $agg['total_engagement'], $agg['post_count']);

      // media_count: jumlah item yang diproses (posts + reels)
      $mediaCount = $agg['post_count'];

      return [
         'followers' => $followers,
         'media_count' => $mediaCount,
         'total_likes' => $agg['total_likes'],
         'total_comments' => $agg['total_comments'],
         'total_shares' => $agg['total_shares'] ?? 0,
         'total_reach' => $agg['total_reach'],
         'engagement_rate' => $derived['engagement_rate'],
         'reach_ratio' => $derived['reach_ratio'],
         'engagement_per_post' => $derived['engagement_per_post'],
         'post_count' => $agg['post_count'],
      ];
   }

   public function storeMetrics(SocialAccount $account, array $metrics, $date = null)
   {
      $date = $date ?? now()->toDateString();
      return Metric::updateOrCreate(
         [
            'social_account_id' => $account->id,
            'provider' => 'facebook',
            'date' => $date,
         ],
         array_merge($metrics, [
            'social_account_id' => $account->id,
            'provider' => 'facebook',
            'date' => $date,
         ])
      );
   }
}
