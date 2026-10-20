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

    public function test_an_organizer_can_view_consolidated_dashboard(): void
    {
        $organizer = User::factory()->create(['role' => 'organizer']);
        Team::create(['name' => 'Ponte Pendente', 'code' => 'ADMIN001', 'category' => 'civil', 'quota_type' => 'civil', 'status' => 'pending', 'audit_status' => 'waiting']);
        Team::create(['name' => 'Ponte Aprovada', 'code' => 'ADMIN002', 'category' => 'civil', 'quota_type' => 'civil', 'status' => 'approved', 'audit_status' => 'approved']);
        Judge::create(['name' => 'Juiz', 'email' => 'juiz@example.com', 'credential' => 'JZ-ADMIN001', 'vote_status' => 'completed']);
        CompetitionSetting::create(['voting_open' => true]);

        $this->actingAs($organizer)->get(route('admin.dashboard'))->assertOk()->assertInertia(fn ($page) => $page
            ->component('Admin/Dashboard')
            ->where('metrics.activeTeams', 2)
            ->where('metrics.pendingRegistrations', 1)
            ->where('metrics.approvedAudits', 1)
            ->where('metrics.completedVotes', 1)
            ->where('metrics.votingOpen', true)
            ->has('alerts'));
    }

    public function test_user_without_an_organizer_role_cannot_view_admin_dashboard(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'viewer']))
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }
}
