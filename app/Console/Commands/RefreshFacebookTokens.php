<?php

namespace App\Console\Commands;

use App\Models\SocialAccount;
use App\Services\FacebookService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RefreshFacebookTokens extends Command
{
    protected FacebookService $facebookService;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:refresh-facebook-tokens';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh all Facebook long-lived access tokens';

    public function __construct(FacebookService $facebookService)
    {
        parent::__construct();
        $this->facebookService = $facebookService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $accounts = SocialAccount::where('provider', 'facebook')->get();

        foreach ($accounts as $account) {
            $expiresAt = $account->expires_at ? Carbon::parse($account->expires_at) : null;
            $daysLeft = $expiresAt ? now()->diffInDays($expiresAt, false) : null;

            if ($daysLeft === null || $daysLeft < 7) {
                try {
                    $oldToken = $account->access_token;
                    [$newToken, $expiresAt] = $this->facebookService->refreshAccessToken($account->access_token);
                    $account->access_token = $newToken;
                    $account->expires_at = $expiresAt;
                    $account->save();
                    $this->info("Berhasil refresh token untuk user_id: {$account->user_id} - {$oldToken} -> {$newToken}");
                } catch (\Exception $e) {
                    $this->error("Gagal refresh token untuk user_id: {$account->user_id} - {$e->getMessage()}");
                }
            } else {
                $this->info("Token user_id: {$account->user_id} masih berlaku > 7 hari, skip refresh.");
            }
        }
    }
}
