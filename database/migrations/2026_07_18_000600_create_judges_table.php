<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('judges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('credential', 20)->unique();
            $table->string('vote_status', 20)->default('not_started');
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('judges'); }
};
