<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('social_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('provider');
            $table->string('provider_id');
            $table->string('name')->nullable();
            $table->string('avatar')->nullable();
            $table->text('access_token');
            $table->timestamp('expires_at')->nullable(); 
            $table->timestamps();

            $table->unique(['provider', 'provider_id'], 'social_accounts_provider_provider_id_unique');
            $table->unique(['user_id', 'provider'], 'social_accounts_user_id_provider_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_accounts');
    }
};
