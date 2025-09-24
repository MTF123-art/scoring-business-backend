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
}
