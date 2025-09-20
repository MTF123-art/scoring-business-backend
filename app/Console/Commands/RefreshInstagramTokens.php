<?php

namespace App\Console\Commands;

use App\Models\SocialAccount;
use App\Services\InstagramService;
use Illuminate\Console\Command;

class RefreshInstagramTokens extends Command
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
    protected $signature = 'app:refresh-instagram-tokens';

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
                $oldToken = $account->access_token;
                [$newToken, $expiresAt] = $this->instagramService->refreshAccessToken($account->access_token);
                $account->access_token = $newToken;
                $account->expires_at = $expiresAt;
                $account->save();
                $this->info("Berhasil refresh token untuk user_id: {$account->user_id}");
                $this->info("Berhasil refresh token untuk user_id: {$account->user_id} - {$oldToken} -> {$newToken}");
            } catch (\Exception $e) {
                $this->error("Gagal refresh token untuk user_id: {$account->user_id} - {$e->getMessage()}");
            }
        }
    }
}
