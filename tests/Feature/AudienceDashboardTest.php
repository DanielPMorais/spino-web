<?php

namespace Tests\Feature;

use App\Models\Judge;
use App\Models\JudgeVote;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AudienceDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_dashboard_calculates_aesthetic_ranking(): void
    {
        $teams = collect(range(1, 3))->map(fn ($number) => Team::create(['name' => "Equipe $number", 'code' => "PLACAR0$number", 'category' => 'civil', 'quota_type' => 'civil', 'status' => 'approved', 'audit_status' => 'approved']));
        $judge = Judge::create(['name' => 'Juiz', 'email' => 'placar@example.com', 'credential' => 'JZ-PLACAR01', 'vote_status' => 'completed']);
        JudgeVote::create(['judge_id' => $judge->id, 'first_team_id' => $teams[0]->id, 'second_team_id' => $teams[1]->id, 'third_team_id' => $teams[2]->id]);

        $this->get(route('audience.dashboard'))->assertOk()->assertInertia(fn ($page) => $page
            ->component('Public/Dashboard')
            ->where('completedJudges', 1)
            ->where('ranking.0.name', 'Equipe 1')
            ->where('ranking.0.aesthetic', 15)
            ->has('ranking', 3));
    }
}
