<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
        Gate::define('manage-registrations', function ($user): bool {
            $administratorEmails = array_map(
                fn (string $email) => mb_strtolower(trim($email)),
                config('competition.admin_emails'),
            );

            return in_array(mb_strtolower(trim($user->email)), $administratorEmails, true);
        });
    }
}
