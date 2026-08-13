<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->string('audit_status', 20)->default('waiting')->after('status');
            $table->unsignedInteger('measured_weight_grams')->nullable()->after('audit_status');
            $table->unsignedInteger('declared_load_grams')->nullable()->after('measured_weight_grams');
            $table->boolean('materials_compliant')->nullable()->after('declared_load_grams');
            $table->boolean('dimensions_compliant')->nullable()->after('materials_compliant');
            $table->boolean('no_coating')->nullable()->after('dimensions_compliant');
            $table->text('audit_notes')->nullable()->after('no_coating');
            $table->timestamp('audited_at')->nullable()->after('audit_notes');
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn(['audit_status', 'measured_weight_grams', 'declared_load_grams', 'materials_compliant', 'dimensions_compliant', 'no_coating', 'audit_notes', 'audited_at']);
        });
    }
};
