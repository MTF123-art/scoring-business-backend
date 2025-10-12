<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class RunDailyMetricsPipeline extends Command
{
   /**
    * The name and signature of the console command.
    *
    * @var string
    */
   protected $signature = 'app:run-daily-metrics-pipeline';

   /**
    * The console command description.
    *
    * @var string
    */
   protected $description = 'Run IG/FB metrics fetching and score calculation in order';

   /**
    * Execute the console command.
    */
   public function handle(): int
   {
      $this->info('Starting daily metrics pipeline...');

      $steps = [
         'app:fetch-instagram-metrics' => 'Fetching Instagram metrics',
         'app:fetch-facebook-metrics'  => 'Fetching Facebook metrics',
         'app:calculate-scores'        => 'Calculating scores',
      ];

      foreach ($steps as $command => $label) {
         $this->info($label . '...');
         $exit = Artisan::call($command);
         $output = Artisan::output();
         $this->line($output);
         if ($exit !== 0) {
            $this->error("Step failed: {$command} (exit code {$exit})");
            return $exit;
         }
      }

      $this->info('Daily metrics pipeline completed.');
      return 0;
   }
}
