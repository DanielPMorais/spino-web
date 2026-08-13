<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('judge_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('judge_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('first_team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('second_team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('third_team_id')->constrained('teams')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('judge_votes'); }
};
