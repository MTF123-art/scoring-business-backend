<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Score;
use App\Services\ScoreService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ScoreController extends Controller
{
    protected ScoreService $scoreService;

    public function __construct(ScoreService $scoreService)
    {
        $this->scoreService = $scoreService;
    }

    public function getScore(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return api_error('unauthenticated', 401);
            }

            $date = Carbon::today()->toDateString();

            $score = Score::where('business_id', $user->id)
                ->whereDate('date', $date)
                ->first();

            if ($score) {
                return api_success($score->toArray(), 'score (cached)');
            }

            $score = $this->scoreService->calculateForBusiness($user->id, $date);
            return api_success($score->toArray(), 'score berhasil dihitung');
        } catch (\Exception $e) {
            return api_error('gagal mengambil score', 500, $e->getMessage());
        }
    }

    public function getLeaderboard(Request $request, $period)
    {
        if (!in_array($period, ['daily', 'weekly'])) {
            return api_error('periode tidak valid. Gunakan daily atau weekly', 400);
        }

        if ($period === 'daily') {
            $date = Carbon::today()->toDateString();
            $leaderboard = Score::whereDate('date', $date)
                ->orderByDesc('final_score')
                ->with('user:id,name,avatar_url')
                ->get();
        } else {
            $start = Carbon::now()->startOfWeek();
            $end = Carbon::today();
            $leaderboard = Score::selectRaw('business_id, AVG(final_score) as avg_score')
                ->whereBetween('date', [$start, $end])
                ->groupBy('business_id')
                ->orderByDesc('avg_score')
                ->with(['user:id,name,avatar_url'])
                ->get();
        }

        $data = $leaderboard->map(function ($item) use ($period) {
            return [
                'name' => $item->user->name ?? null,
                'score' => $period === 'weekly' ? $item->avg_score : $item->final_score,
                'avatar_url' => isset($item->user) ? user_avatar_url($item->user) : null,
            ];
        });

        return api_success($data, "leaderboard {$period}", 200);
    }
}
