<?php

namespace Tests\Feature;

use App\Models\Team;
use App\Models\TestDevice;
use App\Models\TestRun;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManualRuptureResultTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_administrator_can_record_a_manual_rupture_result_for_an_armed_run(): void
    {
        config()->set('competition.admin_emails', ['admin@ifsp.edu.br']);
        $admin = User::factory()->create(['email' => 'admin@ifsp.edu.br']);
        [$team, $run] = $this->teamWithRun('Equipe Backup', 'BACKUP01');

        $this->actingAs($admin)
            ->post(route('admin.manual-rupture-results.store', $team), [
                'manual_rupture_load_grams' => 4520,
                'manual_rupture_notes' => 'Registro manual da comissão.',
            ])
            ->assertRedirect(route('admin.manual-rupture-results.index'));

        $team->refresh();
        $this->assertSame(4520, $team->manual_rupture_load_grams);
        $this->assertSame($admin->id, $team->manual_rupture_recorded_by);
        $this->assertSame('completed', $team->test_status);
        $this->assertNull($team->actual_load_grams);
        $this->assertSame(4520, $run->fresh()->manual_rupture_load_grams);
        $this->assertSame('finished', $run->fresh()->status);
    }

    public function test_a_manual_result_does_not_replace_the_sensor_result(): void
    {
        config()->set('competition.admin_emails', ['admin@ifsp.edu.br']);
        $admin = User::factory()->create(['email' => 'admin@ifsp.edu.br']);
        [$team] = $this->teamWithRun('Equipe Sensor', 'SENSOR01', 4700);

        $this->actingAs($admin)
            ->post(route('admin.manual-rupture-results.store', $team), [
                'manual_rupture_load_grams' => 4680,
            ])
            ->assertSessionHasNoErrors();

        $team->refresh();
        $this->assertSame(4700, $team->actual_load_grams);
        $this->assertSame(4680, $team->manual_rupture_load_grams);
    }

    private function teamWithRun(string $name, string $code, ?int $actualLoad = null): array
    {
        $team = Team::create([
            'name' => $name, 'code' => $code, 'category' => 'general', 'quota_type' => 'general',
            'status' => 'approved', 'audit_status' => 'approved', 'actual_load_grams' => $actualLoad,
        ]);
        $device = TestDevice::create(['code' => "M2-{$code}"]);
        $run = TestRun::create([
            'uuid' => fake()->uuid(),
            'team_id' => $team->id,
            'test_device_id' => $device->id,
            'status' => 'armed',
            'armed_at' => now(),
        ]);

        return [$team, $run];
    }
}
