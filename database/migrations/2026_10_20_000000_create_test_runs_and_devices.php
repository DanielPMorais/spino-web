<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_devices', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
            $table->string('name')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });

        Schema::create('test_runs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('test_device_id')->constrained('test_devices')->restrictOnDelete();
            $table->string('status', 16)->default('armed');
            $table->timestamp('armed_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('manual_rupture_load_grams')->nullable();
            $table->text('manual_rupture_notes')->nullable();
            $table->timestamp('manual_rupture_recorded_at')->nullable();
            $table->foreignId('manual_rupture_recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['team_id', 'status']);
            $table->index(['test_device_id', 'status']);
        });

        Schema::table('bridge_load_samples', function (Blueprint $table) {
            $table->foreignId('test_run_id')->nullable()->after('team_id')->constrained('test_runs')->nullOnDelete();
            $table->unsignedInteger('sequence')->nullable()->after('elapsed_ms');
            $table->unique(['test_run_id', 'sequence']);
        });
    }

    public function down(): void
    {
        Schema::table('bridge_load_samples', function (Blueprint $table) {
            $table->dropUnique(['test_run_id', 'sequence']);
            $table->dropForeign(['test_run_id']);
            $table->dropColumn(['test_run_id', 'sequence']);
        });

        Schema::dropIfExists('test_runs');
        Schema::dropIfExists('test_devices');
    }
};
