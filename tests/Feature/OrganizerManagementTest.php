<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizerManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_admin_can_create_and_remove_an_organizer(): void
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

        $organizer = User::query()->where('email', 'ana@ifsp.edu.br')->firstOrFail();
        $this->assertSame('organizer', $organizer->role);

        $this->actingAs($admin)
            ->delete(route('admin.organizers.destroy', $organizer))
            ->assertRedirect(route('admin.organizers.index'));

        $this->assertDatabaseMissing('users', ['id' => $organizer->id]);
    }

    public function test_an_organizer_cannot_manage_platform_users(): void
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
