<?php

namespace Tests\Feature;

use App\Models\BridgeLoadSample;
use App\Models\Team;
use App\Models\TestDevice;
use App\Models\TestRun;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SensorCaptureApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_records_a_sample_for_the_armed_test_run(): void
    {
        [$team, $device, $run] = $this->armedRun();

        $this->postJson('/api/v1/sensors/capture', [
            'test_run_id' => $run->uuid,
            'device_code' => $device->code,
            'sequence' => 1,
            'elapsed_ms' => 1250,
            'load_grams' => 3200,
            'timestamp' => '2026-10-20T12:00:00+00:00',
        ])->assertAccepted()->assertJsonPath('accepted', true);

        $this->assertDatabaseHas('bridge_load_samples', ['team_id' => $team->id, 'test_run_id' => $run->id, 'sequence' => 1, 'elapsed_ms' => 1250, 'load_grams' => 3200]);
        $this->assertSame('running', $run->fresh()->status);
        $this->assertSame('running', $team->fresh()->test_status);
    }

    public function test_it_completes_a_test_using_the_highest_observed_load(): void
    {
        [$team, $device, $run] = $this->armedRun();
        BridgeLoadSample::create(['team_id' => $team->id, 'test_run_id' => $run->id, 'sequence' => 1, 'elapsed_ms' => 1000, 'load_grams' => 4500]);

        $this->postJson('/api/v1/sensors/capture', [
            'test_run_id' => $run->uuid,
            'device_code' => $device->code,
            'sequence' => 2,
            'event' => 'completed',
            'elapsed_ms' => 1600,
            'load_grams' => 4000,
        ])->assertAccepted();

        $team->refresh();
        $this->assertSame('finished', $run->fresh()->status);
        $this->assertSame('completed', $team->test_status);
        $this->assertSame(4500, $team->actual_load_grams);
    }

    public function test_it_rejects_a_capture_from_the_wrong_device(): void
    {
        [, , $run] = $this->armedRun();

        $this->postJson('/api/v1/sensors/capture', [
            'test_run_id' => $run->uuid,
            'device_code' => 'M2-OUTRO',
            'sequence' => 1,
            'elapsed_ms' => 0,
            'load_grams' => 0,
        ])->assertUnprocessable();
    }

    public function test_a_duplicate_sequence_is_idempotent_but_conflicts_when_its_payload_changes(): void
    {
        [, $device, $run] = $this->armedRun();
        $payload = ['test_run_id' => $run->uuid, 'device_code' => $device->code, 'sequence' => 1, 'elapsed_ms' => 100, 'load_grams' => 700];

        $this->postJson('/api/v1/sensors/capture', $payload)
            ->assertAccepted()->assertJsonPath('duplicate', false);
        $this->postJson('/api/v1/sensors/capture', $payload)
            ->assertAccepted()->assertJsonPath('duplicate', true);
        $this->postJson('/api/v1/sensors/capture', [...$payload, 'load_grams' => 800])
            ->assertUnprocessable();
    }

    public function test_it_returns_the_active_run_for_the_registered_device(): void
    {
        [$team, $device, $run] = $this->armedRun();

        $this->getJson("/api/v1/sensors/devices/{$device->code}/active-run")
            ->assertOk()
            ->assertJsonPath('active', true)
            ->assertJsonPath('run_id', $run->uuid)
            ->assertJsonPath('team.code', $team->code);
    }

    public function test_it_rejects_a_capture_for_an_unknown_run_without_a_token(): void
    {
        $this->postJson('/api/v1/sensors/capture', [
            'test_run_id' => '9e8a8f9c-9d0c-4e89-8013-5b453c564e31', 'device_code' => 'M2-01',
            'sequence' => 1, 'elapsed_ms' => 0, 'load_grams' => 0,
        ])->assertUnprocessable();
    }

    private function armedRun(): array
    {
        $team = Team::create([
            'name' => 'Equipe API', 'code' => 'PONTE001', 'category' => 'civil', 'quota_type' => 'civil',
            'status' => 'approved', 'audit_status' => 'approved',
        ]);
        $device = TestDevice::create(['code' => 'M2-01']);
        $run = TestRun::create([
            'uuid' => '9e8a8f9c-9d0c-4e89-8013-5b453c564e31',
            'team_id' => $team->id,
            'test_device_id' => $device->id,
            'status' => 'armed',
            'armed_at' => now(),
        ]);

        return [$team, $device, $run];
    }
}
