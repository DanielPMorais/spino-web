<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_student_with_an_academic_email_can_create_a_team(): void
    {
        Mail::fake();

        $response = $this->post(route('registration.store'), [
            'team_name' => 'Ponte de Teste',
            'name' => 'Líder de Teste',
            'course' => 'Bacharelado em Engenharia Civil',
            'email' => 'lider@aluno.ifsp.edu.br',
        ]);

        $response->assertRedirect(route('registration.create'));
        $this->assertDatabaseHas('teams', ['name' => 'Ponte de Teste', 'status' => 'forming']);
        $this->assertDatabaseHas('members', ['email' => 'lider@aluno.ifsp.edu.br', 'is_leader' => true]);
        $response->assertSessionMissing('teamCode');
    }

    public function test_a_temporary_mail_failure_keeps_the_registration_recoverable(): void
    {
        Mail::shouldReceive('raw')->once()->andThrow(new RuntimeException('SMTP indisponível.'));

        $response = $this->post(route('registration.store'), [
            'team_name' => 'Ponte com Reenvio',
            'name' => 'Líder de Teste',
            'course' => 'Bacharelado em Engenharia Civil',
            'email' => 'reenvio@aluno.ifsp.edu.br',
        ]);

        $response->assertRedirect(route('registration.create'));
        $response->assertSessionHas('registration_verification_member_id');
        $this->assertDatabaseHas('members', ['email' => 'reenvio@aluno.ifsp.edu.br', 'is_leader' => true]);
    }

    public function test_a_student_cannot_join_more_than_one_team(): void
    {
        $team = Team::create(['name' => 'Primeira Ponte', 'code' => 'ABC12345', 'category' => 'general', 'quota_type' => 'general']);
        Member::create(['team_id' => $team->id, 'enrollment' => 'CT900002', 'name' => 'Já Inscrito', 'course' => 'Técnico em Edificações', 'email' => 'inscrito@aluno.ifsp.edu.br', 'is_leader' => true]);

        $response = $this->post(route('registration.join'), ['code' => 'ABC12345', 'name' => 'Já Inscrito', 'course' => 'Técnico em Edificações', 'email' => 'inscrito@aluno.ifsp.edu.br']);

        $response->assertSessionHasErrors('email');
        $this->assertDatabaseCount('members', 1);
    }

    public function test_a_person_without_an_academic_email_cannot_create_a_team(): void
    {
        $this->post(route('registration.store'), [
            'team_name' => 'Ponte Inelegível', 'name' => 'Estudante Inelegível', 'course' => 'Engenharia Civil', 'email' => 'inelegivel@gmail.com',
        ])->assertSessionHasErrors('email');

        $this->assertDatabaseCount('teams', 0);
    }

    public function test_a_team_name_can_only_be_registered_once(): void
    {
        Team::create(['name' => 'Ponte Única', 'code' => 'UNICA123', 'category' => 'general', 'quota_type' => 'general']);

        $this->post(route('registration.store'), [
            'team_name' => '  Ponte   Única  ',
            'name' => 'Novo Líder',
            'course' => 'Técnico em Edificações',
            'email' => 'novo@aluno.ifsp.edu.br',
        ])->assertSessionHasErrors('team_name');
    }

    public function test_a_member_must_confirm_the_email_code_before_the_team_is_pending(): void
    {
        $team = Team::create(['name' => 'Ponte Confirmada', 'code' => 'ABC12345', 'category' => 'general', 'quota_type' => 'general']);
        Member::create([
            'team_id' => $team->id,
            'name' => 'Líder Confirmado',
            'course' => 'Técnico em Edificações',
            'email' => 'lider@aluno.ifsp.edu.br',
            'is_leader' => true,
            'email_verified_at' => now(),
        ]);
        $member = Member::create([
            'team_id' => $team->id,
            'name' => 'Membro Confirmado',
            'course' => 'Técnico em Edificações',
            'email' => 'membro@aluno.ifsp.edu.br',
            'email_verification_code' => Hash::make('123456'),
            'email_verification_expires_at' => now()->addMinutes(15),
        ]);

        $this->withSession(['registration_verification_member_id' => $member->id])
            ->post(route('registration.verify-email'), ['code' => '123456'])
            ->assertRedirect(route('registration.create'));

        $this->assertNotNull($member->refresh()->email_verified_at);
        $this->assertDatabaseHas('teams', ['id' => $team->id, 'status' => 'pending']);
    }

    public function test_unconfirmed_drafts_do_not_consume_a_registration_vacancy(): void
    {
        Team::create(['name' => 'Rascunho', 'code' => 'RASC0003', 'category' => 'general', 'quota_type' => 'general', 'status' => 'forming']);
        Team::create(['name' => 'Inscrita', 'code' => 'PEND0002', 'category' => 'general', 'quota_type' => 'general', 'status' => 'pending']);

        $this->get(route('registration.create'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Registration/Create')
                ->where('availability.totalRemaining', config('competition.max_teams') - 1));
    }

    public function test_a_leader_receives_the_team_code_after_confirming_the_email(): void
    {
        Mail::fake();

        $team = Team::create(['name' => 'Ponte da Liderança', 'code' => 'LIDER123', 'category' => 'general', 'quota_type' => 'general']);
        $leader = Member::create([
            'team_id' => $team->id,
            'name' => 'Líder da Equipe',
            'course' => 'Bacharelado em Engenharia Civil',
            'email' => 'lider@aluno.ifsp.edu.br',
            'is_leader' => true,
            'email_verification_code' => Hash::make('123456'),
            'email_verification_expires_at' => now()->addMinutes(15),
        ]);

        $this->withSession(['registration_verification_member_id' => $leader->id])
            ->post(route('registration.verify-email'), ['code' => '123456'])
            ->assertRedirect(route('registration.create'))
            ->assertSessionHas('teamCode', 'LIDER123');
    }
}
