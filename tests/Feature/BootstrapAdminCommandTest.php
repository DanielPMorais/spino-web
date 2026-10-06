<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BootstrapAdminCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_the_configured_administrator(): void
    {
        config()->set('competition.admin_emails', ['organizacao@example.com']);
        config()->set('competition.bootstrap_admin', [
            'email' => 'organizacao@example.com',
            'name' => 'Comissão organizadora',
            'password' => 'senha-inicial-segura',
        ]);

        $this->artisan('competition:bootstrap-admin --force')
            ->expectsOutput('Conta administrativa criada para organizacao@example.com.')
            ->assertSuccessful();

        $user = User::query()->where('email', 'organizacao@example.com')->firstOrFail();

        $this->assertTrue(Hash::check('senha-inicial-segura', $user->password));
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_it_does_not_replace_an_existing_administrator_password(): void
    {
        config()->set('competition.admin_emails', ['organizacao@example.com']);
        config()->set('competition.bootstrap_admin', [
            'email' => 'organizacao@example.com',
            'name' => 'Comissão organizadora',
            'password' => 'nova-senha-segura',
        ]);

        $user = User::factory()->create(['email' => 'organizacao@example.com', 'password' => 'senha-atual-segura']);

        $this->artisan('competition:bootstrap-admin --force')
            ->expectsOutput('A conta administrativa já existe. A senha atual foi preservada.')
            ->assertSuccessful();

        $this->assertTrue(Hash::check('senha-atual-segura', $user->fresh()->password));
    }

    public function test_it_requires_the_bootstrap_email_to_be_an_administrator(): void
    {
        config()->set('competition.admin_emails', ['outro@example.com']);
        config()->set('competition.bootstrap_admin', [
            'email' => 'organizacao@example.com',
            'name' => 'Comissão organizadora',
            'password' => 'senha-inicial-segura',
        ]);

        $this->artisan('competition:bootstrap-admin --force')
            ->expectsOutput('Inclua BOOTSTRAP_ADMIN_EMAIL também em COMPETITION_ADMIN_EMAILS.')
            ->assertFailed();

        $this->assertDatabaseMissing('users', ['email' => 'organizacao@example.com']);
    }
}
