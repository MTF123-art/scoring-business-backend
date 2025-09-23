<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('metrics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('social_account_id');
            $table->string('provider'); 
            $table->date('date');
            $table->integer('followers')->nullable();
            $table->integer('media_count')->nullable();
            $table->integer('total_likes')->nullable();
            $table->integer('total_comments')->nullable();
            $table->integer('total_reach')->nullable();
            $table->float('engagement_rate')->nullable();
            $table->float('reach_ratio')->nullable();
            $table->float('engagement_per_post')->nullable();
            $table->float('post_count')->nullable();
            $table->timestamps();

            $table->foreign('social_account_id')->references('id')->on('social_accounts')->onDelete('cascade');
            $table->unique(['social_account_id', 'date', 'provider']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metrics');
    }
};
