<?php

namespace Tests\Feature;

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_admin_can_create_a_team_with_confirmed_participants(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.teams.store'), [
                'name' => 'Ponte Manual',
                'code' => 'MANUAL01',
                'members' => [
                    ['name' => 'Líder Manual', 'email' => 'lider@aluno.ifsp.edu.br', 'course' => 'Tecnologia em Análise e Desenvolvimento de Sistemas'],
                    ['name' => 'Integrante Manual', 'email' => 'integrante@aluno.ifsp.edu.br', 'course' => 'Bacharelado em Engenharia Civil'],
                ],
            ])
            ->assertRedirect(route('admin.teams.index'));

        $this->assertDatabaseHas('teams', ['name' => 'Ponte Manual', 'code' => 'MANUAL01', 'status' => 'pending']);
        $this->assertDatabaseHas('members', ['email' => 'lider@aluno.ifsp.edu.br', 'is_leader' => true]);
        $this->assertDatabaseHas('members', ['email' => 'integrante@aluno.ifsp.edu.br', 'is_leader' => false]);
    }

    public function test_an_admin_can_delete_a_team_by_confirming_its_code(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $team = Team::create(['name' => 'Ponte de Teste', 'code' => 'APAGAR01', 'category' => 'general', 'quota_type' => 'general']);

        $this->actingAs($admin)
            ->delete(route('admin.teams.destroy', $team), ['confirmation_code' => 'APAGAR01'])
            ->assertRedirect(route('admin.teams.index'));

        $this->assertDatabaseMissing('teams', ['id' => $team->id]);
    }

    public function test_an_organizer_cannot_manage_teams_directly(): void
    {
        $organizer = User::factory()->create(['role' => 'organizer']);

        $this->actingAs($organizer)->get(route('admin.teams.index'))->assertForbidden();
    }
}
