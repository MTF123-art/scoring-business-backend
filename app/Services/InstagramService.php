<?php

namespace App\Services;

use App\Models\SocialAccount;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Log\LogManager;
use Laravel\Socialite\Contracts\User as SocialiteUser;


class InstagramService
{
    protected string $baseUrl = 'https://graph.instagram.com';
    protected $http;
    protected $log;

    public function __construct(HttpFactory $http, LogManager $log)
    {
        $this->http = $http;
        $this->log = $log;
    }

    public function connectAccount(int $userId, SocialiteUser $instagramUser)
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

    protected function exchangeForLongLivedToken(string $shortLivedToken)
    {
        $response = $this->http->get($this->baseUrl . '/access_token', [
            'grant_type' => 'ig_exchange_token',
            'client_secret' => config('services.instagram.client_secret'),
            'access_token' => $shortLivedToken,
        ]);
        if ($response->failed()) {
            $this->log->error('Instagram token exchange failed', [
                'response_status' => $response->status(),
                'response_body' => $response->body(),
            ]);
            throw new \Exception('Failed to exchange token: ' . $response->body());
        }
        $data = $response->json();
        $token = $data['access_token'];
        $expiresIn = $data['expires_in'];
        $expiresAt = now()->addSeconds($expiresIn);
        return [$token, $expiresAt];
    }

    public function refreshAccessToken(string $longLivedToken)
    {
        $response = $this->http->get($this->baseUrl . '/refresh_access_token', [
            'grant_type' => 'ig_refresh_token',
            'access_token' => $longLivedToken,
        ]);
        if ($response->failed()) {
            $this->log->error('Instagram token refresh failed', [
                'response_status' => $response->status(),
                'response_body' => $response->body(),
            ]);
            throw new \Exception('Failed to refresh token: ' . $response->body());
        }
        $data = $response->json();
        $token = $data['access_token'];
        $expiresIn = $data['expires_in'];
        $expiresAt = now()->addSeconds($expiresIn);
        return [$token, $expiresAt];
    }

    public function getMetrics(SocialAccount $account)
    {
        $accessToken = $account->access_token;
        $userId = $account->provider_id;

        $profileResponse = $this->http->get("{$this->baseUrl}/{$userId}", [
            'fields' => 'id,username,followers_count,media_count',
            'access_token' => $accessToken,
        ]);
        if ($profileResponse->failed()) {
            $this->log->error('Gagal mengambil profil Instagram', [
                'response_status' => $profileResponse->status(),
                'response_body' => $profileResponse->body(),
            ]);
            throw new \Exception('Gagal mengambil data profil Instagram');
        }
        $profile = $profileResponse->json();
        $followers = $profile['followers_count'] ?? 0;
        $mediaCount = $profile['media_count'] ?? 0;

        $mediaResponse = $this->http->get("{$this->baseUrl}/{$userId}/media", [
            'fields' => 'id,caption,like_count,comments_count',
            'access_token' => $accessToken,
        ]);
        if ($mediaResponse->failed()) {
            $this->log->error('Gagal mengambil media Instagram', [
                'response_status' => $mediaResponse->status(),
                'response_body' => $mediaResponse->body(),
            ]);
            throw new \Exception('Gagal mengambil data media Instagram');
        }
        $mediaData = $mediaResponse->json();
        $posts = $mediaData['data'] ?? [];

        $totalLikes = 0;
        $totalComments = 0;
        $totalReach = 0;
        $totalEngagement = 0;
        $postCount = count($posts);

        foreach ($posts as $post) {
            $likes = $post['like_count'] ?? 0;
            $comments = $post['comments_count'] ?? 0;
            $reach = $this->http->get("{$this->baseUrl}/{$post['id']}/insights", [
                'metric' => 'reach',
                'access_token' => $accessToken,
            ]);
            if ($reach->failed()) {
                $this->log->error('Gagal mengambil media Instagram', [
                    'response_status' => $reach->status(),
                    'response_body' => $reach->body(),
                ]);
                throw new \Exception('Gagal mengambil data media Instagram');
            }
            $reach = $reach->json();
            $reach = $reach['data'][0]['values'][0]['value'] ?? 0;
            $totalLikes += $likes;
            $totalComments += $comments;
            $totalReach += $reach;
            $totalEngagement += $likes + $comments; // share tidak tersedia di IG Graph API
        }

        // Engagement Rate 
        $engagementRate = $followers > 0 ? (($totalLikes + $totalComments) / $followers) * 100 : 0;
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
