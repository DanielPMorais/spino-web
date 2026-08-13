<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->unsignedInteger('actual_load_grams')->nullable()->after('audited_at');
            $table->decimal('efficiency_score', 8, 2)->nullable()->after('actual_load_grams');
            $table->decimal('precision_score', 8, 2)->nullable()->after('efficiency_score');
            $table->string('test_status', 20)->default('pending')->after('precision_score');
            $table->timestamp('tested_at')->nullable()->after('test_status');
        });

        Schema::create('bridge_load_samples', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('elapsed_ms');
            $table->unsignedInteger('load_grams');
            $table->timestamp('created_at')->useCurrent();
            $table->index(['team_id', 'elapsed_ms']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bridge_load_samples');
        Schema::table('teams', fn (Blueprint $table) => $table->dropColumn(['actual_load_grams', 'efficiency_score', 'precision_score', 'test_status', 'tested_at']));
    }
};
