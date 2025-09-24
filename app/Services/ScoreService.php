<?php

namespace App\Services;

use App\Models\Metric;
use App\Models\Score;
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

      $igMetric = Metric::where('provider', 'instagram')
         ->whereHas('socialAccount', function ($q) use ($businessId) {
            $q->where('user_id', $businessId);
         })
         ->whereDate('date', $date)
         ->latest()
         ->first();

      $fbMetric = Metric::where('provider', 'facebook')
         ->whereHas('socialAccount', function ($q) use ($businessId) {
            $q->where('user_id', $businessId);
         })
         ->whereDate('date', $date)
         ->latest()
         ->first();

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
