<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->unsignedInteger('manual_rupture_load_grams')->nullable();
            $table->text('manual_rupture_notes')->nullable();
            $table->timestamp('manual_rupture_recorded_at')->nullable();
            $table->foreignId('manual_rupture_recorded_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropForeign(['manual_rupture_recorded_by']);
            $table->dropColumn([
                'manual_rupture_load_grams',
                'manual_rupture_notes',
                'manual_rupture_recorded_at',
                'manual_rupture_recorded_by',
            ]);
        });
    }
};
