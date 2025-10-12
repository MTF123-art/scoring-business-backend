<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($jumlah = 1; $jumlah <= 10; $jumlah++) {
            User::create([
                'name' => "bisnis $jumlah",
                'email' => "bisnis$jumlah@example.com",
                'password' => Hash::make('12345678'),
            ]);
        }
    }
}