<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->string('category', 30)->after('code');
        });

        Schema::table('members', function (Blueprint $table) {
            $table->string('email')->after('course');
        });
    }

    public function down(): void
    {
        Schema::table('members', fn (Blueprint $table) => $table->dropColumn('email'));
        Schema::table('teams', fn (Blueprint $table) => $table->dropColumn('category'));
    }
};
