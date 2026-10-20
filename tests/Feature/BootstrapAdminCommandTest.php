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
        $this->assertSame('admin', $user->role);
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_it_promotes_an_existing_account_without_replacing_its_password(): void
    {
        config()->set('competition.bootstrap_admin', [
            'email' => 'organizacao@example.com',
            'name' => 'Comissão organizadora',
            'password' => 'nova-senha-segura',
        ]);

        $user = User::factory()->create([
            'email' => 'organizacao@example.com',
            'password' => 'senha-atual-segura',
            'role' => 'organizer',
        ]);

        $this->artisan('competition:bootstrap-admin --force')
            ->expectsOutput('A conta já existia. A senha atual foi preservada e a conta foi promovida a administrador.')
            ->assertSuccessful();

        $this->assertTrue(Hash::check('senha-atual-segura', $user->fresh()->password));
        $this->assertSame('admin', $user->fresh()->role);
    }
}
