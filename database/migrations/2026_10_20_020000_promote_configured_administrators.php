<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $emails = collect([
            ...config('competition.admin_emails', []),
            config('competition.bootstrap_admin.email'),
        ])
            ->filter(fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL))
            ->map(fn (string $email) => mb_strtolower(trim($email)))
            ->unique()
            ->values();

        if ($emails->isNotEmpty()) {
            DB::table('users')
                ->whereIn('email', $emails->all())
                ->update(['role' => 'admin']);
        }
    }

    public function down(): void
    {
        // Do not demote accounts on rollback: their role may have been changed
        // deliberately after this migration ran.
    }
};
