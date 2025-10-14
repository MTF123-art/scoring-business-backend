<?php

namespace App\Services;

use App\Models\Metric;
use App\Models\Score;
use App\Models\SocialAccount;
use Carbon\Carbon;

class ScoreService
{
   public function calculateForBusiness($businessId, $date = null)
   {
      $date = $date ?? Carbon::today();
      // Pastikan format tanggal konsisten (Y-m-d)
      if ($date instanceof Carbon) {
         $date = $date->toDateString();
      }


      $accountIds = SocialAccount::where('user_id', $businessId)->pluck('id')->all();

      $igMetric = Metric::whereIn('provider', ['instagram', 'dummy_instagram'])
         ->whereIn('social_account_id', $accountIds)
         ->whereDate('date', $date)
         ->orderByDesc('id')
         ->first();

      $fbMetric = Metric::whereIn('provider', ['facebook', 'dummy_facebook'])
         ->whereIn('social_account_id', $accountIds)
         ->whereDate('date', $date)
         ->orderByDesc('id')
         ->first();

      if (app()->runningInConsole()) {
         echo "\n[DEBUG] User: $businessId\n";
         echo "Account IDs: " . json_encode($accountIds) . "\n";
         echo "IG Metric: " . ($igMetric ? json_encode($igMetric->toArray()) : 'null') . "\n";
         echo "FB Metric: " . ($fbMetric ? json_encode($fbMetric->toArray()) : 'null') . "\n";
      }

      $instagramScore = $igMetric ? $this->scorePlatform($igMetric) : 0;
      $facebookScore = $fbMetric ? $this->scorePlatform($fbMetric) : 0;

      $finalScore = ($instagramScore + $facebookScore) / 2;

      return Score::updateOrCreate(
         ['business_id' => $businessId, 'date' => $date],
         [
            'instagram_score' => $instagramScore,
            'facebook_score'  => $facebookScore,
            'final_score'     => $finalScore,
         ]
      );
   }

   private function scorePlatform($metric)
   {
      $ER  = $this->normalize($metric->engagement_rate, 0, 10);
      $RR  = $this->normalize($metric->reach_ratio, 0, 5);
      $EPP = $this->normalize($metric->engagement_per_post, 0, 500);

      $score = (0.4 * $ER) + (0.3 * $RR) + (0.3 * $EPP);

      return round($score, 2);
   }

   private function normalize($value, $min, $max)
   {
      if ($value <= $min) {
         return 0;
      }
      if ($value >= $max) {
         return 100;
      }
      return (($value - $min) / ($max - $min)) * 100;
   }
}
