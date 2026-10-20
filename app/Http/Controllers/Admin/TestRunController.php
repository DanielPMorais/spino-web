<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\TestDevice;
use App\Models\TestRun;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class TestRunController extends Controller
{
    public function index(): Response
    {
        $teams = Team::query()
            ->where('audit_status', 'approved')
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'test_status']);

        $devices = TestDevice::query()
            ->orderBy('code')
            ->get()
            ->map(fn (TestDevice $device) => [
                'id' => $device->id,
                'code' => $device->code,
                'name' => $device->name,
                'lastSeenAt' => $device->last_seen_at?->toIso8601String(),
            ]);

        $runs = TestRun::query()
            ->with(['team:id,name,code', 'device:id,code'])
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn (TestRun $run) => [
                'id' => $run->id,
                'uuid' => $run->uuid,
                'status' => $run->status,
                'team' => ['name' => $run->team->name, 'code' => $run->team->code],
                'deviceCode' => $run->device->code,
                'armedAt' => $run->armed_at?->toIso8601String(),
                'startedAt' => $run->started_at?->toIso8601String(),
                'finishedAt' => $run->finished_at?->toIso8601String(),
            ]);

        return Inertia::render('Admin/TestRuns/Index', compact('teams', 'devices', 'runs'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'team_id' => ['required', 'integer', 'exists:teams,id'],
            'device_code' => ['required', 'string', 'max:32', 'regex:/^[A-Za-z0-9_-]+$/'],
        ]);

        $team = Team::findOrFail($data['team_id']);
        abort_unless($team->audit_status === 'approved', 422, 'A ponte precisa estar aprovada antes de armar o ensaio.');

        DB::transaction(function () use ($team, $data, $request): void {
            $deviceCode = strtoupper($data['device_code']);
            $device = TestDevice::firstOrCreate(['code' => $deviceCode]);

            if (TestRun::query()->where('team_id', $team->id)->whereIn('status', ['armed', 'running'])->exists()) {
                throw ValidationException::withMessages(['team_id' => 'Esta equipe já possui um ensaio ativo.']);
            }

            if (TestRun::query()->where('test_device_id', $device->id)->whereIn('status', ['armed', 'running'])->exists()) {
                throw ValidationException::withMessages(['device_code' => 'Este M2 já está vinculado a outro ensaio ativo.']);
            }

            TestRun::create([
                'uuid' => (string) Str::uuid(),
                'team_id' => $team->id,
                'test_device_id' => $device->id,
                'status' => 'armed',
                'armed_at' => now(),
                'created_by' => $request->user()->id,
            ]);
        });

        return to_route('admin.test-runs.index')->with('success', 'Ensaio armado. Aguarde a confirmação do M2 antes de iniciar a ruptura.');
    }

    public function start(TestRun $testRun): RedirectResponse
    {
        abort_unless($testRun->status === 'armed', 422, 'Somente ensaios armados podem ser iniciados.');

        $testRun->update(['status' => 'running', 'started_at' => now()]);
        $testRun->team->update(['test_status' => 'running']);

        return to_route('admin.test-runs.index')->with('success', 'Ensaio iniciado.');
    }

    public function finish(TestRun $testRun): RedirectResponse
    {
        abort_unless(in_array($testRun->status, ['armed', 'running'], true), 422, 'Este ensaio já foi encerrado.');

        $testRun->update(['status' => 'finished', 'finished_at' => now()]);

        return to_route('admin.test-runs.index')->with('success', 'Ensaio encerrado. Registre o peso manual em Rupturas se necessário.');
    }

    public function cancel(TestRun $testRun): RedirectResponse
    {
        abort_unless(in_array($testRun->status, ['armed', 'running'], true), 422, 'Este ensaio não pode mais ser cancelado.');

        $testRun->update(['status' => 'cancelled', 'cancelled_at' => now()]);

        return to_route('admin.test-runs.index')->with('success', 'Ensaio cancelado; nenhuma leitura desse vínculo será aceita.');
    }
}
