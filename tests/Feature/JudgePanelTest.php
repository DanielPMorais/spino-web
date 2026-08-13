<?php

namespace Tests\Feature;

use App\Models\CompetitionSetting;
use App\Models\Judge;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JudgePanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_judge_can_submit_one_ranking_while_voting_is_open(): void
    {
        CompetitionSetting::create(['voting_open' => true]);
        $judge = Judge::create(['name' => 'Juiz Teste', 'email' => 'juiz@example.com', 'credential' => 'JZ-TESTE123']);
        $teams = collect(range(1, 3))->map(fn ($number) => Team::create(['name' => "Equipe $number", 'code' => "EQUIPE0$number", 'category' => 'civil', 'quota_type' => 'civil', 'status' => 'approved', 'audit_status' => 'approved']));

        $response = $this->post(route('judge.vote.store', $judge->credential), [
            'first_team_id' => $teams[0]->id,
            'second_team_id' => $teams[1]->id,
            'third_team_id' => $teams[2]->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('judge_votes', ['judge_id' => $judge->id, 'first_team_id' => $teams[0]->id]);
        $this->assertDatabaseHas('judges', ['id' => $judge->id, 'vote_status' => 'completed']);
    }

    public function test_judge_cannot_vote_twice(): void
    {
        CompetitionSetting::create(['voting_open' => true]);
        $judge = Judge::create(['name' => 'Juiz Teste', 'email' => 'juiz@example.com', 'credential' => 'JZ-TESTE123']);
        $teams = collect(range(1, 3))->map(fn ($number) => Team::create(['name' => "Equipe $number", 'code' => "EQUIPE0$number", 'category' => 'civil', 'quota_type' => 'civil', 'status' => 'approved', 'audit_status' => 'approved']));
        $payload = ['first_team_id' => $teams[0]->id, 'second_team_id' => $teams[1]->id, 'third_team_id' => $teams[2]->id];

        $this->post(route('judge.vote.store', $judge->credential), $payload);
        $this->post(route('judge.vote.store', $judge->credential), $payload)->assertSessionHasErrors('voting');
        $this->assertDatabaseCount('judge_votes', 1);
    }
}
