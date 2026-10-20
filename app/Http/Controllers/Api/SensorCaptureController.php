<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BridgeLoadSample;
use App\Models\TestRun;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SensorCaptureController extends Controller
{
    /** Recebe uma leitura do M2 vinculada a um ensaio armado no M3. */
    public function __invoke(Request $request): JsonResponse
    {
        $expectedToken = (string) config('sensors.token');

        if ($expectedToken === '' || ! hash_equals($expectedToken, (string) $request->bearerToken())) {
            return response()->json(['message' => 'Não autorizado.'], 401);
        }

        $data = $request->validate([
            'test_run_id' => ['required', 'uuid'],
            'device_code' => ['required', 'string', 'max:32'],
            'sequence' => ['required', 'integer', 'min:1'],
            'event' => ['nullable', 'in:sample,started,completed'],
            'elapsed_ms' => ['required', 'integer', 'min:0', 'max:86400000'],
            'load_grams' => ['required', 'integer', 'min:0', 'max:1000000'],
            'timestamp' => ['nullable', 'date'],
        ]);

        $capturedAt = isset($data['timestamp']) ? CarbonImmutable::parse($data['timestamp']) : now();
        $event = $data['event'] ?? 'sample';

        $result = DB::transaction(function () use ($data, $capturedAt, $event): array {
            $run = TestRun::query()
                ->with(['team', 'device'])
                ->where('uuid', $data['test_run_id'])
                ->lockForUpdate()
                ->first();

            if (! $run) {
                throw ValidationException::withMessages(['test_run_id' => 'Ensaio não encontrado.']);
            }

            if ($run->device->code !== strtoupper($data['device_code'])) {
                throw ValidationException::withMessages(['device_code' => 'Este M2 não pertence ao ensaio informado.']);
            }

            if (! in_array($run->status, ['armed', 'running'], true)) {
                throw ValidationException::withMessages(['test_run_id' => 'O ensaio não está aceitando novas leituras.']);
            }

            if ($run->team->audit_status !== 'approved') {
                throw ValidationException::withMessages(['test_run_id' => 'A equipe não está aprovada para ensaio.']);
            }

            $run->device->update(['last_seen_at' => now()]);

            $existing = $run->samples()->where('sequence', $data['sequence'])->first();
            if ($existing) {
                if ($existing->load_grams !== $data['load_grams'] || $existing->elapsed_ms !== $data['elapsed_ms']) {
                    throw ValidationException::withMessages(['sequence' => 'A sequência já foi usada com outra leitura.']);
                }

                return ['run' => $run, 'duplicate' => true];
            }

            BridgeLoadSample::create([
                'team_id' => $run->team_id,
                'test_run_id' => $run->id,
                'sequence' => $data['sequence'],
                'elapsed_ms' => $data['elapsed_ms'],
                'load_grams' => $data['load_grams'],
                'created_at' => $capturedAt,
            ]);

            if ($event === 'completed') {
                $peakLoad = max((int) $data['load_grams'], (int) ($run->samples()->max('load_grams') ?? 0));
                $run->update(['status' => 'finished', 'started_at' => $run->started_at ?? $capturedAt, 'finished_at' => $capturedAt]);
                $run->team->update([
                    'test_status' => 'completed',
                    'actual_load_grams' => $peakLoad,
                    'tested_at' => $capturedAt,
                ]);
            } else {
                $run->update(['status' => 'running', 'started_at' => $run->started_at ?? $capturedAt]);
                $run->team->update(['test_status' => 'running']);
            }

            return ['run' => $run->fresh(['team', 'device']), 'duplicate' => false];
        });

        return response()->json([
            'accepted' => true,
            'duplicate' => $result['duplicate'],
            'test_run_id' => $result['run']->uuid,
            'event' => $event,
        ], 202);
    }
}
