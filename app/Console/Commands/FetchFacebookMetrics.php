<?php

namespace App\Console\Commands;

use App\Models\SocialAccount;
use App\Services\FacebookService;
use Illuminate\Console\Command;


class FetchFacebookMetrics extends Command
{
    protected FacebookService $facebookService;

    public function __construct(FacebookService $facebookService)
    {
        parent::__construct();
        $this->facebookService = $facebookService;
    }
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-facebook-metrics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $accounts = SocialAccount::where('provider', 'facebook')->get();

        foreach ($accounts as $account) {
            try {
                $metrics = $this->facebookService->getMetrics($account);
                $this->facebookService->storeMetrics($account, $metrics);
                $this->info("Berhasil ambil metrik untuk user_id: {$account->user_id}");
                $this->info("Data Metrik: " . json_encode($metrics));
            } catch (\Exception $e) {
                $this->error("Gagal ambil metrik untuk user_id: {$account->user_id} - {$e->getMessage()}");
            }
        }
    }
}
