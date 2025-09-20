<?php

namespace App\Console\Commands;

use App\Models\SocialAccount;
use App\Services\InstagramService;
use Illuminate\Console\Command;

class FetchInstagramMetrics extends Command
{
    protected InstagramService $instagramService;

    public function __construct(InstagramService $instagramService)
    {
        parent::__construct();
        $this->instagramService = $instagramService;
    }
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-instagram-metrics';

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
        $accounts = SocialAccount::where('provider', 'instagram')->get();

        foreach ($accounts as $account) {
            try {
                $metrics = $this->instagramService->getMetrics($account);
                // Simpan metrics ke db
                $this->info("Berhasil ambil metrik untuk user_id: {$account->user_id}");
                $this->info("Data Metrik: " . json_encode($metrics));
            } catch (\Exception $e) {
                $this->error("Gagal ambil metrik untuk user_id: {$account->user_id} - {$e->getMessage()}");
            }
        }
    }
}
