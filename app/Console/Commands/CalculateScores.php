<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\ScoreService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CalculateScores extends Command
{
   protected ScoreService $scoreService;

   public function __construct(ScoreService $scoreService)
   {
      parent::__construct();
      $this->scoreService = $scoreService;
   }

   /**
    * The name and signature of the console command.
    *
    * @var string
    */
   protected $signature = 'app:calculate-scores {--date=}';

   /**
    * The console command description.
    *
    * @var string
    */
   protected $description = 'Hitung skor bisnis berdasarkan metrik Instagram & Facebook untuk hari ini atau tanggal tertentu';

   /**
    * Execute the console command.
    */
   public function handle()
   {
      $date = $this->option('date') ?? Carbon::today()->toDateString();
      $users = User::all();
      foreach ($users as $user) {
         try {
            $score = $this->scoreService->calculateForBusiness($user->id, $date);
            $this->info("Skor user ID {$user->id} tanggal {$date}: " . json_encode($score->toArray()));
         } catch (\Exception $e) {
            $this->error("Gagal hitung skor user ID {$user->id}: {$e->getMessage()}");
         }
      }
   }
}
