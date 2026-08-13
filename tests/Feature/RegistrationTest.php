<?php

namespace Tests\Feature;

use App\Models\CampusStudent;
use App\Models\Member;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_active_campus_student_can_create_a_team(): void
    {
        CampusStudent::create([
            'enrollment' => 'CT900001',
            'name' => 'Líder de Teste',
            'course' => 'Engenharia Civil',
            'is_active' => true,
        ]);

        $response = $this->post(route('registration.store'), [
            'team_name' => 'Ponte de Teste',
            'category' => 'civil',
            'email' => 'lider@ifsp.edu.br',
            'enrollment' => 'CT900001',
        ]);

        $response->assertRedirect(route('registration.create'));
        $this->assertDatabaseHas('teams', ['name' => 'Ponte de Teste', 'quota_type' => 'civil', 'status' => 'forming']);
        $this->assertDatabaseHas('members', ['enrollment' => 'CT900001', 'is_leader' => true]);
    }

    public function test_a_student_cannot_join_more_than_one_team(): void
    {
        $team = Team::create(['name' => 'Primeira Ponte', 'code' => 'ABC12345', 'category' => 'general', 'quota_type' => 'general']);
        Member::create(['team_id' => $team->id, 'enrollment' => 'CT900002', 'name' => 'Já Inscrito', 'course' => 'Outro', 'email' => 'inscrito@ifsp.edu.br', 'is_leader' => true]);
        CampusStudent::create(['enrollment' => 'CT900002', 'name' => 'Já Inscrito', 'course' => 'Outro', 'is_active' => true]);

        $response = $this->post(route('registration.join'), ['code' => 'ABC12345', 'course' => 'Outro', 'email' => 'inscrito@ifsp.edu.br', 'enrollment' => 'CT900002']);

        $response->assertSessionHasErrors('enrollment');
        $this->assertDatabaseCount('members', 1);
    }
}
