<?php

namespace Tests\Feature;

use App\Models\BridgeLoadSample;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SensorCaptureApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('sensors.token', 'raspberry-test-token');
    }

    public function test_it_records_a_sample_and_marks_the_team_as_running(): void
    {
        $team = $this->approvedTeam();

        $this->withToken('raspberry-test-token')->postJson('/api/v1/sensors/capture', [
            'team_code' => $team->code,
            'elapsed_ms' => 1250,
            'load_grams' => 3200,
            'timestamp' => '2026-09-03T12:00:00+00:00',
        ])->assertAccepted()->assertJsonPath('accepted', true);

        $this->assertDatabaseHas('bridge_load_samples', ['team_id' => $team->id, 'elapsed_ms' => 1250, 'load_grams' => 3200]);
        $this->assertSame('running', $team->fresh()->test_status);
    }

    public function test_it_completes_a_test_using_the_highest_observed_load(): void
    {
        $team = $this->approvedTeam();
        BridgeLoadSample::create(['team_id' => $team->id, 'elapsed_ms' => 1000, 'load_grams' => 4500]);

        $this->withToken('raspberry-test-token')->postJson('/api/v1/sensors/capture', [
            'team_code' => $team->code,
            'event' => 'completed',
            'elapsed_ms' => 1600,
            'load_grams' => 4000,
        ])->assertAccepted();

        $team->refresh();
        $this->assertSame('completed', $team->test_status);
        $this->assertSame(4500, $team->actual_load_grams);
    }

    public function test_it_rejects_a_missing_or_invalid_token(): void
    {
        $this->postJson('/api/v1/sensors/capture', [
            'team_code' => 'PONTE001', 'elapsed_ms' => 0, 'load_grams' => 0,
        ])->assertUnauthorized();
    }

    private function approvedTeam(): Team
    {
        return Team::create([
            'name' => 'Equipe API', 'code' => 'PONTE001', 'category' => 'civil', 'quota_type' => 'civil',
            'status' => 'approved', 'audit_status' => 'approved',
        ]);
    }
}
