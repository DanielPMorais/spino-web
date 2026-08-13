<?php

namespace Tests\Feature;

use App\Models\CompetitionSetting;
use App\Models\Judge;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_configured_administrator_can_view_consolidated_dashboard(): void
    {
        config()->set('competition.admin_emails', ['admin@ifsp.edu.br']);
        $admin = User::factory()->create(['email' => 'admin@ifsp.edu.br']);
        Team::create(['name' => 'Ponte Pendente', 'code' => 'ADMIN001', 'category' => 'civil', 'quota_type' => 'civil', 'status' => 'pending', 'audit_status' => 'waiting']);
        Team::create(['name' => 'Ponte Aprovada', 'code' => 'ADMIN002', 'category' => 'civil', 'quota_type' => 'civil', 'status' => 'approved', 'audit_status' => 'approved']);
        Judge::create(['name' => 'Juiz', 'email' => 'juiz@example.com', 'credential' => 'JZ-ADMIN001', 'vote_status' => 'completed']);
        CompetitionSetting::create(['voting_open' => true]);

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()->assertInertia(fn ($page) => $page
            ->component('Admin/Dashboard')
            ->where('metrics.activeTeams', 2)
            ->where('metrics.pendingRegistrations', 1)
            ->where('metrics.approvedAudits', 1)
            ->where('metrics.completedVotes', 1)
            ->where('metrics.votingOpen', true)
            ->has('alerts'));
    }

    public function test_non_administrator_cannot_view_admin_dashboard(): void
    {
        config()->set('competition.admin_emails', ['admin@ifsp.edu.br']);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }
}
