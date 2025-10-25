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
        $accounts = SocialAccount::where('provider', 'facebook')->orWhere('provider', 'dummy_facebook')->get();

        foreach ($accounts as $account) {

            if($account->provider === 'facebook'){
                try {
                    $metrics = $this->facebookService->getMetrics($account);
                    $this->facebookService->storeMetrics($account, $metrics);
                    $this->info("Berhasil ambil metrik untuk user_id: {$account->user_id}");
                    $this->info("Data Metrik: " . json_encode($metrics));
                } catch (\Exception $e) {
                    $this->error("Gagal ambil metrik untuk user_id: {$account->user_id} - {$e->getMessage()}");
                }
            }elseif ($account->provider === 'dummy_facebook'){
                $followers = rand(100, 1000);
                $mediaCount = rand(10, 100);
                $totalLikes = rand(1000, 10000);
                $totalComments = rand(100, 1000);
                $totalReach = rand(1000, 10000);
                $totalShares = rand(100, 1000);
                $derived = [
                    'engagement_rate' => $followers > 0 ? ($totalLikes + $totalComments + $totalShares) / $followers * 100 : 0,
                    'reach_ratio' => $followers > 0 ? $totalReach / $followers : 0,
                    'engagement_per_post' => $mediaCount > 0 ? ($totalLikes + $totalComments + $totalShares) / $mediaCount : 0,
                ];
                $metrics = [
                    'followers' => $followers,
                    'media_count' => $mediaCount,
                    'total_likes' => $totalLikes,
                    'total_comments' => $totalComments,
                    'total_reach' => $totalReach,
                    'total_shares' => $totalShares,
                    'engagement_rate' => $derived['engagement_rate'],
                    'reach_ratio' => $derived['reach_ratio'],
                    'engagement_per_post' => $derived['engagement_per_post'],
                    'post_count' => $mediaCount,
                ];
                try {
                    $this->facebookService->storeMetrics($account, $metrics);
                    $this->info("Berhasil ambil metrik dummy untuk user_id: {$account->user_id}");
                    $this->info("Data Metrik: " . json_encode($metrics));
                } catch (\Exception $e) {
                    $this->error("Gagal ambil metrik dummy untuk user_id: {$account->user_id} - {$e->getMessage()}");
                }
            }
        }
    }
}
