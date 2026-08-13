<?php

namespace Database\Seeders;

use App\Models\BridgeLoadSample;
use App\Models\CampusStudent;
use App\Models\CompetitionSetting;
use App\Models\Judge;
use App\Models\JudgeVote;
use App\Models\Member;
use App\Models\Team;
use Illuminate\Database\Seeder;

class DemoCompetitionSeeder extends Seeder
{
    public function run(): void
    {
        $definitions = [
            ['code' => 'MOCK001', 'name' => 'Palitos Mágicos', 'quota_type' => 'civil', 'status' => 'approved', 'audit_status' => 'approved', 'weight' => 985, 'declared' => 22000, 'load' => 29500, 'ee' => 24.30, 'pp' => 8.20, 'test_status' => 'completed'],
            ['code' => 'MOCK002', 'name' => 'Palitos Atômicos', 'quota_type' => 'civil', 'status' => 'approved', 'audit_status' => 'approved', 'weight' => 1002, 'declared' => 25000, 'load' => 27400, 'ee' => 22.10, 'pp' => 9.70, 'test_status' => 'completed'],
            ['code' => 'MOCK003', 'name' => 'Palitos Alvinegros', 'quota_type' => 'civil', 'status' => 'approved', 'audit_status' => 'approved', 'weight' => 973, 'declared' => 24000, 'load' => 26800, 'ee' => 21.80, 'pp' => 9.50, 'test_status' => 'completed'],
            ['code' => 'MOCK004', 'name' => 'Viga Mestra', 'quota_type' => 'general', 'status' => 'approved', 'audit_status' => 'approved', 'weight' => 998, 'declared' => 23000, 'load' => null, 'ee' => null, 'pp' => null, 'test_status' => 'running'],
            ['code' => 'MOCK005', 'name' => 'Arco Forte', 'quota_type' => 'civil', 'status' => 'approved', 'audit_status' => 'waiting', 'weight' => null, 'declared' => null, 'load' => null, 'ee' => null, 'pp' => null, 'test_status' => 'pending'],
            ['code' => 'MOCK006', 'name' => 'Ponte Nova', 'quota_type' => 'general', 'status' => 'pending', 'audit_status' => 'waiting', 'weight' => null, 'declared' => null, 'load' => null, 'ee' => null, 'pp' => null, 'test_status' => 'pending'],
            ['code' => 'MOCK007', 'name' => 'Treliça 360', 'quota_type' => 'civil', 'status' => 'approved', 'audit_status' => 'rejected', 'weight' => 1032, 'declared' => 20000, 'load' => null, 'ee' => null, 'pp' => null, 'test_status' => 'pending'],
            ['code' => 'MOCK008', 'name' => 'Equilíbrio', 'quota_type' => 'general', 'status' => 'forming', 'audit_status' => 'waiting', 'weight' => null, 'declared' => null, 'load' => null, 'ee' => null, 'pp' => null, 'test_status' => 'pending'],
        ];

        $teams = collect();
        foreach ($definitions as $index => $definition) {
            $approvedAudit = $definition['audit_status'] === 'approved';
            $team = Team::updateOrCreate(['code' => $definition['code']], [
                'name' => $definition['name'], 'category' => $definition['quota_type'], 'quota_type' => $definition['quota_type'],
                'status' => $definition['status'], 'audit_status' => $definition['audit_status'],
                'measured_weight_grams' => $definition['weight'], 'declared_load_grams' => $definition['declared'],
                'materials_compliant' => $approvedAudit, 'dimensions_compliant' => $approvedAudit, 'no_coating' => $approvedAudit,
                'audit_notes' => $definition['audit_status'] === 'rejected' ? 'Peso acima da tolerância regulamentar.' : null,
                'audited_at' => $definition['weight'] ? now()->subHours(10 - $index) : null,
                'actual_load_grams' => $definition['load'], 'efficiency_score' => $definition['ee'], 'precision_score' => $definition['pp'],
                'test_status' => $definition['test_status'], 'tested_at' => $definition['test_status'] === 'completed' ? now()->subHours(4 - $index) : null,
            ]);
            $teams->put($definition['code'], $team);

            $enrollment = 'MCK'.str_pad((string) ($index + 1), 5, '0', STR_PAD_LEFT);
            $leaderName = ['Ana Souza', 'Bruno Lima', 'Carla Mendes', 'Diego Rocha', 'Elisa Martins', 'Felipe Costa', 'Gabriela Luz', 'Henrique Alves'][$index];
            $course = $definition['quota_type'] === 'civil' ? 'Engenharia Civil' : 'Arquitetura e Urbanismo';
            CampusStudent::updateOrCreate(['enrollment' => $enrollment], ['name' => $leaderName, 'course' => $course, 'is_active' => true]);
            Member::updateOrCreate(['enrollment' => $enrollment], ['team_id' => $team->id, 'name' => $leaderName, 'course' => $course, 'email' => strtolower(str_replace(' ', '.', $leaderName)).'@demo.ifsp.edu.br', 'is_leader' => true]);
        }

        $judges = collect([
            ['name' => 'Alberto Siqueira', 'email' => 'alberto@demo.com', 'credential' => 'JZ-DEMO001', 'vote_status' => 'completed'],
            ['name' => 'Marina Campos', 'email' => 'marina@demo.com', 'credential' => 'JZ-DEMO002', 'vote_status' => 'completed'],
            ['name' => 'Rafael Nunes', 'email' => 'rafael@demo.com', 'credential' => 'JZ-DEMO003', 'vote_status' => 'not_started'],
        ])->map(fn ($data) => Judge::updateOrCreate(['email' => $data['email']], $data));

        JudgeVote::updateOrCreate(['judge_id' => $judges[0]->id], ['first_team_id' => $teams['MOCK001']->id, 'second_team_id' => $teams['MOCK002']->id, 'third_team_id' => $teams['MOCK003']->id]);
        JudgeVote::updateOrCreate(['judge_id' => $judges[1]->id], ['first_team_id' => $teams['MOCK002']->id, 'second_team_id' => $teams['MOCK003']->id, 'third_team_id' => $teams['MOCK001']->id]);

        $settings = CompetitionSetting::first() ?? new CompetitionSetting();
        $settings->voting_open = true;
        $settings->save();

        $samples = [[0, 0], [5000, 2800], [10000, 5100], [15000, 7300], [20000, 10800], [25000, 12600], [30000, 15400], [35000, 17100], [40000, 19800]];
        foreach ($samples as [$elapsed, $load]) {
            BridgeLoadSample::updateOrCreate(['team_id' => $teams['MOCK004']->id, 'elapsed_ms' => $elapsed], ['load_grams' => $load]);
        }

        $this->command?->info('Dados de demonstração inseridos. Juiz disponível: JZ-DEMO003');
    }
}
