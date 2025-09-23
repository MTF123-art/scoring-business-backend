<?php

namespace App\Services;

use App\Models\Metric;
use App\Models\SocialAccount;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Log\LogManager;
use Laravel\Socialite\Contracts\User as SocialiteUser;


class InstagramService
{
    protected string $baseUrl = 'https://graph.instagram.com/v23.0';
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

    /**
     * Ambil profil IG (followers_count, media_count)
     */
    protected function fetchProfile(string $userId, string $accessToken): array
    {
        $profileResponse = $this->http->get("{$this->baseUrl}/{$userId}", [
            'fields' => 'followers_count,media_count',
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
        return [
            'followers' => (int)($profile['followers_count'] ?? 0),
            'media_count' => (int)($profile['media_count'] ?? 0),
        ];
    }

    /**
     * Ambil daftar ID media (post) IG
     */
    protected function fetchMediaIds(string $userId, string $accessToken): array
    {
        $mediaResponse = $this->http->get("{$this->baseUrl}/{$userId}/media", [
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
        // return only valid IDs
        return array_values(array_filter(array_map(static function ($post) {
            return $post['id'] ?? null;
        }, $posts)));
    }

    /**
     * Ambil insights per media: likes, comments, reach (shares diabaikan untuk saat ini)
     */
    protected function fetchMediaInsights(string $mediaId, string $accessToken): array
    {
        $insightsResp = $this->http->get("{$this->baseUrl}/{$mediaId}/insights", [
            'metric' => 'likes,comments,shares,reach',
            'access_token' => $accessToken,
        ]);
        if ($insightsResp->failed()) {
            $this->log->warning('Gagal mengambil insights media Instagram, default 0', [
                'media_id' => $mediaId,
                'response_status' => $insightsResp->status(),
                'response_body' => $insightsResp->body(),
            ]);
            return ['likes' => 0, 'comments' => 0, 'reach' => 0];
        }

        $likes = 0;
        $comments = 0;
        $reach = 0;
        $data = $insightsResp->json()['data'] ?? [];
        foreach ($data as $metric) {
            $name = $metric['name'] ?? '';
            $value = (int)($metric['values'][0]['value'] ?? 0);
            if ($name === 'likes') {
                $likes = $value;
            } elseif ($name === 'comments') {
                $comments = $value;
            } elseif ($name === 'reach') {
                $reach = $value;
            }
            // shares tersedia, tapi tidak tersimpan pada tabel metrics saat ini
        }

        return [
            'likes' => $likes,
            'comments' => $comments,
            'reach' => $reach,
        ];
    }

    /**
     * Agregasi metrik dari semua media
     */
    protected function aggregateMediaMetrics(array $mediaIds, string $accessToken): array
    {
        $totalLikes = 0;
        $totalComments = 0;
        $totalReach = 0;
        $postCount = count($mediaIds);

        foreach ($mediaIds as $mediaId) {
            $ins = $this->fetchMediaInsights($mediaId, $accessToken);
            $totalLikes += $ins['likes'];
            $totalComments += $ins['comments'];
            $totalReach += $ins['reach'];
            // shares diabaikan untuk saat ini
        }

        return [
            'total_likes' => $totalLikes,
            'total_comments' => $totalComments,
            'total_reach' => $totalReach,
            'total_engagement' => $totalLikes + $totalComments,
            'post_count' => $postCount,
        ];
    }

    /**
     * Hitung metrik turunan (engagement rate, reach ratio, engagement per post)
     */
    protected function computeDerivedMetrics(int $followers, int $totalLikes, int $totalComments, int $totalReach, int $postCount): array
    {
        $engagementRate = $followers > 0 ? (($totalLikes + $totalComments) / $followers) * 100 : 0;
        $reachRatio = $followers > 0 ? $totalReach / $followers : 0;
        $engagementPerPost = $postCount > 0 ? ($totalLikes + $totalComments) / $postCount : 0;

        return [
            'engagement_rate' => round($engagementRate, 2),
            'reach_ratio' => round($reachRatio, 2),
            'engagement_per_post' => round($engagementPerPost, 2),
        ];
    }

    public function getMetrics(SocialAccount $account)
    {
        $accessToken = $account->access_token;
        $userId = $account->provider_id;

        $profile = $this->fetchProfile($userId, $accessToken);
        $mediaIds = $this->fetchMediaIds($userId, $accessToken);
        $agg = $this->aggregateMediaMetrics($mediaIds, $accessToken);
        $derived = $this->computeDerivedMetrics(
            $profile['followers'],
            $agg['total_likes'],
            $agg['total_comments'],
            $agg['total_reach'],
            $agg['post_count']
        );

        return [
            'followers' => $profile['followers'],
            'media_count' => $profile['media_count'],
            'total_likes' => $agg['total_likes'],
            'total_comments' => $agg['total_comments'],
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
                'provider' => 'instagram',
                'date' => $date,
            ],
            array_merge($metrics, [
                'social_account_id' => $account->id,
                'provider' => 'instagram',
                'date' => $date,
            ])
        );
    }
}
