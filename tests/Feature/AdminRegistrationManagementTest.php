<?php

namespace Tests\Feature;

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRegistrationManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_organizers_can_manage_registrations(): void
    {
        Team::create(['name' => 'Ponte Forte', 'code' => 'PONTE001', 'category' => 'civil', 'quota_type' => 'civil', 'status' => 'pending']);
        $organizer = User::factory()->create(['role' => 'organizer']);

        $this->actingAs($organizer)->get(route('admin.registrations.index'))->assertOk()->assertInertia(fn ($page) => $page->component('Admin/Registrations/Index')->has('teams', 1));
        $this->actingAs(User::factory()->create(['role' => 'viewer']))->get(route('admin.registrations.index'))->assertForbidden();
    }

    public function test_organizer_can_update_a_team_status(): void
    {
        $team = Team::create(['name' => 'Ponte Forte', 'code' => 'PONTE002', 'category' => 'civil', 'quota_type' => 'civil', 'status' => 'pending']);

        $this->actingAs(User::factory()->create(['role' => 'organizer']))->patch(route('admin.registrations.status', $team), ['status' => 'approved'])->assertRedirect();
        $this->assertDatabaseHas('teams', ['id' => $team->id, 'status' => 'approved']);
    }

    public function test_unconfirmed_teams_are_not_visible_or_counted_as_registrations(): void
    {
        Team::create(['name' => 'Rascunho', 'code' => 'RASC0001', 'category' => 'civil', 'quota_type' => 'civil', 'status' => 'forming']);
        Team::create(['name' => 'Confirmada', 'code' => 'PEND0001', 'category' => 'civil', 'quota_type' => 'civil', 'status' => 'pending']);

        $this->actingAs(User::factory()->create(['role' => 'organizer']))
            ->get(route('admin.registrations.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Registrations/Index')
                ->has('teams', 1)
                ->where('metrics.total', 1));
    }

    public function test_organizer_cannot_change_the_status_of_an_unconfirmed_team(): void
    {
        $team = Team::create(['name' => 'Rascunho', 'code' => 'RASC0002', 'category' => 'civil', 'quota_type' => 'civil', 'status' => 'forming']);

        $this->actingAs(User::factory()->create(['role' => 'organizer']))
            ->patch(route('admin.registrations.status', $team), ['status' => 'approved'])
            ->assertNotFound();
    }
}
