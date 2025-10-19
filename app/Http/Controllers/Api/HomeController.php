<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Score;
use App\Models\SocialAccount;
use App\Services\ScoreService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    protected ScoreService $scoreService;

    public function __construct(ScoreService $scoreService)
    {
        $this->scoreService = $scoreService;
    }

    public function index(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return api_error('unauthenticated', 401);
            }

            $now = Carbon::now();
            $today = $now->toDateString();

            $todayScore = Score::where('business_id', $user->id)
                ->whereDate('date', $today)
                ->first();
            if (!$todayScore) {
                $todayScore = $this->scoreService->calculateForBusiness($user->id, $today);
            }

            $igScore = (float)($todayScore->instagram_score ?? 0);
            $fbScore = (float)($todayScore->facebook_score ?? 0);
            $final  = (float)($todayScore->final_score ?? 0);

            $totalToday = Score::whereDate('date', $today)->count();
            $rankPos = null;
            if ($totalToday > 0) {
                $higher = Score::whereDate('date', $today)
                    ->where('final_score', '>', $final)
                    ->count();
                $rankPos = $higher + 1;
            }

            $platforms = [];
            foreach (['instagram', 'facebook'] as $provider) {
                $account = SocialAccount::where('user_id', $user->id)
                    ->where('provider', $provider)
                    ->first();
                $connected = (bool)$account;
                $platforms[] = [
                    'platform' => $provider,
                    'connected' => $connected,
                    'account' => $connected ? ($account->name ?? null) : null,
                ];
            }

            $thisWeekStart = (clone $now)->startOfWeek();
            $lastWeekStart = (clone $thisWeekStart)->subWeek();
            $lastWeekEnd   = (clone $thisWeekStart)->subDay();

            $thisWeekAvg = (float)(
                Score::where('business_id', $user->id)
                ->whereBetween('date', [$thisWeekStart->toDateString(), $today])
                ->avg('final_score') ?? 0
            );
            $lastWeekAvg = (float)(
                Score::where('business_id', $user->id)
                ->whereBetween('date', [$lastWeekStart->toDateString(), $lastWeekEnd->toDateString()])
                ->avg('final_score') ?? 0
            );
            $deltaPercent = $lastWeekAvg > 0
                ? round((($thisWeekAvg - $lastWeekAvg) / $lastWeekAvg) * 100, 2)
                : null;

            $seriesStart = $thisWeekStart->toDateString();
            $scores = Score::where('business_id', $user->id)
                ->whereBetween('date', [$seriesStart, $today])
                ->orderBy('date')
                ->get()
                ->keyBy('date');

            $dailySeries = [];
            $cursor = Carbon::parse($seriesStart);
            $end = Carbon::parse($today);
            while ($cursor->lte($end)) {
                $dateStr = $cursor->toDateString();
                $dailySeries[] = [
                    'date' => $dateStr,
                    'value' => isset($scores[$dateStr]) ? (float)$scores[$dateStr]->final_score : null,
                ];
                $cursor->addDay();
            }

            $payload = [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                ],
                'platforms' => $platforms,
                'today' => [
                    'score' => round($final, 2),
                    'perPlatform' => [
                        ['platform' => 'instagram', 'score' => round($igScore, 2)],
                        ['platform' => 'facebook',  'score' => round($fbScore, 2)],
                    ],
                    'rank' => [
                        'position' => $rankPos,
                        'total' => $totalToday,
                    ],
                ],
                'weeklyComparison' => [
                    'lastWeekAvgScore' => round($lastWeekAvg, 2),
                    'thisWeekAvgScore' => round($thisWeekAvg, 2),
                    'deltaPercent' => $deltaPercent,
                ],
                'charts' => [
                    'dailyScoreSeries' => $dailySeries,
                ],
            ];

            return api_success($payload, 'home');
        } catch (\Exception $e) {
            return api_error('gagal mengambil data home', 500, $e->getMessage());
        }
    }
}
