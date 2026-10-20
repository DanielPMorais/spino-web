<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizerManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_admin_can_create_an_organizer(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.organizers.store'), [
                'name' => 'Ana Organizadora',
                'email' => 'ana@ifsp.edu.br',
                'password' => 'senha-inicial-segura',
                'password_confirmation' => 'senha-inicial-segura',
            ])
            ->assertRedirect(route('admin.organizers.index'));

        $this->assertDatabaseHas('users', [
            'name' => 'Ana Organizadora',
            'email' => 'ana@ifsp.edu.br',
            'role' => 'organizer',
        ]);
    }

    public function test_an_organizer_cannot_create_platform_users(): void
    {
        $organizer = User::factory()->create(['role' => 'organizer']);

        $this->actingAs($organizer)
            ->get(route('admin.organizers.index'))
            ->assertForbidden();

        $this->actingAs($organizer)
            ->post(route('admin.organizers.store'), [
                'name' => 'Outra Pessoa',
                'email' => 'outra@ifsp.edu.br',
                'password' => 'senha-inicial-segura',
                'password_confirmation' => 'senha-inicial-segura',
            ])
            ->assertForbidden();
    }
}
