<?php

namespace Tests\Feature;

use App\Models\Team;
use App\Models\TestDevice;
use App\Models\TestRun;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TestRunManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_organizer_can_arm_a_team_on_an_m2(): void
    {
        $organizer = User::factory()->create(['role' => 'organizer']);
        $team = $this->approvedTeam();

        $this->actingAs($organizer)
            ->post(route('admin.test-runs.store'), ['team_id' => $team->id, 'device_code' => 'm2-01'])
            ->assertRedirect(route('admin.test-runs.index'));

        $this->assertDatabaseHas('test_devices', ['code' => 'M2-01']);
        $this->assertDatabaseHas('test_runs', ['team_id' => $team->id, 'status' => 'armed']);
    }

    public function test_a_team_and_device_cannot_have_two_active_runs(): void
    {
        $organizer = User::factory()->create(['role' => 'organizer']);
        $team = $this->approvedTeam();
        $device = TestDevice::create(['code' => 'M2-01']);
        TestRun::create(['uuid' => '9e8a8f9c-9d0c-4e89-8013-5b453c564e31', 'team_id' => $team->id, 'test_device_id' => $device->id, 'status' => 'armed', 'armed_at' => now()]);

        $this->actingAs($organizer)
            ->from(route('admin.test-runs.index'))
            ->post(route('admin.test-runs.store'), ['team_id' => $team->id, 'device_code' => 'M2-01'])
            ->assertRedirect(route('admin.test-runs.index'))
            ->assertSessionHasErrors('team_id');
    }

    private function approvedTeam(): Team
    {
        return Team::create([
            'name' => 'Equipe Ensaio', 'code' => 'ENSAIO01', 'category' => 'general', 'quota_type' => 'general',
            'status' => 'approved', 'audit_status' => 'approved',
        ]);
    }
}
