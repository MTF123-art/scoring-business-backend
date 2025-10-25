<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SocialAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            $user->socialAccounts()->create([
                'provider' => 'dummy_instagram',
                'provider_id' => 'dummy_instagram_id_' . $user->id,
                'name' => $user->name . ' dummy instagram',
                'avatar' => 'https://example.com/avatar' . $user->id . '.png',
                'access_token' => 'dummy_access_token_' . $user->id,
                'expires_at' => now()->addDays(30),
            ]);
        }

        foreach ($users as $user) {
            $user->socialAccounts()->create([
                'provider' => 'dummy_facebook',
                'provider_id' => 'dummy_facebook_id_' . $user->id,
                'name' => $user->name . ' dummy facebook',
                'avatar' => 'https://example.com/avatar' . $user->id . '.png',
                'access_token' => 'dummy_access_token_' . $user->id,
                'expires_at' => now()->addDays(30),
            ]);
        }
    }
}
