<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Metric;
use App\Models\SocialAccount;
use App\Models\Score;
use App\Services\FacebookService;
use App\Services\ScoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Laravel\Socialite\Facades\Socialite;

class FacebookController extends Controller
{
    protected FacebookService $facebookService;
    protected ScoreService $scoreService;

    public function __construct(FacebookService $facebookService, ScoreService $scoreService)
    {
        $this->facebookService = $facebookService;
        $this->scoreService = $scoreService;
    }

    public function redirectToFacebook(Request $request): JsonResponse
    {
        try {
            $state = Str::random(32);
            Cache::put("oauth_state:{$state}", $request->user()->id, now()->addMinutes(10));
            /** @var \Laravel\Socialite\Two\AbstractProvider $fbDriver */
            $fbDriver = Socialite::driver('facebook');
            $redirectUrl = $fbDriver
                ->stateless()
                ->scopes(['read_insights', 'pages_show_list', 'pages_read_engagement', 'pages_manage_metadata', 'pages_read_user_content', 'pages_manage_posts', 'pages_manage_engagement'])
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
                /** @var \Laravel\Socialite\Two\AbstractProvider $fbDriver */
                $fbDriver = Socialite::driver('facebook');
                $facebookUser = $fbDriver->stateless()->user();
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

    public function fetchOrStoreMetrics(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return api_error('unauthenticated', 401);
            }

            // Temukan akun Facebook milik user
            $account = SocialAccount::where('user_id', $user->id)
                ->where('provider', 'facebook')
                ->first();
            if (!$account) {
                return api_error('akun facebook belum terhubung', 404);
            }

            $today = now()->toDateString();

            // Cek metric di DB untuk hari ini
            $metric = Metric::where('social_account_id', $account->id)
                ->where('provider', 'facebook')
                ->where('date', $today)
                ->first();

            if ($metric) {
                return api_success($metric->toArray(), 'data metric facebook (cached)');
            }

            // Ambil dari service lalu simpan
            try {
                $data = $this->facebookService->getMetrics($account);
            } catch (\Exception $e) {
                return api_error('gagal mengambil metric facebook dari API', 400, $e->getMessage());
            }

            try {
                $metric = $this->facebookService->storeMetrics($account, $data, $today);
            } catch (\Exception $e) {
                return api_error('gagal menyimpan metric facebook', 500, $e->getMessage());
            }

            return api_success($metric->toArray(), 'berhasil mengambil & menyimpan metric facebook');
        } catch (\Exception $e) {
            return api_error('terjadi kesalahan saat mengambil metric facebook', 500, $e->getMessage());
        }
    }

}
