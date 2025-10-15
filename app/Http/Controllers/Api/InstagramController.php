<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Metric;
use App\Models\SocialAccount;
use App\Services\InstagramService;
use App\Services\ScoreService;
use App\Models\Score;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Laravel\Socialite\Facades\Socialite;

class InstagramController extends Controller
{
    protected InstagramService $instagramService;
    protected ScoreService $scoreService;

    public function __construct(InstagramService $instagramService, ScoreService $scoreService)
    {
        $this->instagramService = $instagramService;
        $this->scoreService = $scoreService;
    }

    public function redirectToInstagram(Request $request): JsonResponse
    {
        try {
            $state = Str::random(32);
            Cache::put("oauth_state:{$state}", $request->user()->id, now()->addMinutes(10));
            /** @var \Laravel\Socialite\Two\AbstractProvider $igDriver */
            $igDriver = Socialite::driver('instagram');
            $redirectUrl = $igDriver
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
        } catch (\Exception $e) {
            return api_error('gagal membuat url login instagram', 500, $e->getMessage());
        }
    }

    public function handleCallback(Request $request)
    {
        try {
            $state = $request->query('state');
            $userId = Cache::pull("oauth_state:{$state}");
            if (!$userId) {
                return view('connected')->with(['message' => 'state tidak valid atau kadaluarsa']);
            }
            try {
                /** @var \Laravel\Socialite\Two\AbstractProvider $igDriver */
                $igDriver = Socialite::driver('instagram');
                $instagramUser = $igDriver->stateless()->user();
            } catch (\Exception $e) {
                return view('connected')->with(['message' => 'Gagal mengambil data user instagram: '.$e->getMessage()]);
            }
            try {
                $this->instagramService->connectAccount($userId, $instagramUser);
            } catch (\Exception $e) {
                return view('connected')->with(['message' => 'Gagal menyimpan akun instagram: '.$e->getMessage()]);
            }
            return view('connected')->with(['message' => 'Berhasil terhubung dengan akun instagram']);
        } catch (\Exception $e) {
            return view('connected')->with(['message' => 'Gagal terhubung dengan akun instagram: '.$e->getMessage()]);
        }
    }

    public function fetchOrStoreMetrics(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return api_error('unauthenticated', 401);
            }

            $account = SocialAccount::where('user_id', $user->id)
                ->where('provider', 'instagram')
                ->first();
            if (!$account) {
                return api_error('akun instagram belum terhubung', 404);
            }

            $today = now()->toDateString();

            $metric = Metric::where('social_account_id', $account->id)
                ->where('provider', 'instagram')
                ->where('date', $today)
                ->first();

            $metric = $metric->toArray();
            $metric['username'] = $account->name;

            if ($metric) {
                return api_success($metric, 'data metric instagram (cached)');
            }

            try {
                $data = $this->instagramService->getMetrics($account);
            } catch (\Exception $e) {
                return api_error('gagal mengambil metric instagram dari API', 400, $e->getMessage());
            }

            try {
                $metric = $this->instagramService->storeMetrics($account, $data, $today);
            } catch (\Exception $e) {
                return api_error('gagal menyimpan metric instagram', 500, $e->getMessage());
            }

            $metric = $metric->toArray();
            $metric['username'] = $account->name;

            return api_success($metric, 'berhasil mengambil & menyimpan metric instagram');
        } catch (\Exception $e) {
            return api_error('terjadi kesalahan saat mengambil metric instagram', 500, $e->getMessage());
        }
    }

}
