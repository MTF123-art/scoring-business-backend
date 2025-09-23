<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->date('date');
            $table->float('instagram_score')->nullable();
            $table->float('facebook_score')->nullable();
            $table->float('final_score')->nullable();
            $table->timestamps();

            $table->foreign('business_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['business_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scores');
    }
};