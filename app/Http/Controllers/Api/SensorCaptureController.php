<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BridgeLoadSample;
use App\Models\Team;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SensorCaptureController extends Controller
{
    /** Recebe uma leitura processada pelo Raspberry Pi para o ensaio atual. */
    public function __invoke(Request $request): JsonResponse
    {
        $expectedToken = (string) config('sensors.token');

        if ($expectedToken === '' || ! hash_equals($expectedToken, (string) $request->bearerToken())) {
            return response()->json(['message' => 'Não autorizado.'], 401);
        }

        $data = $request->validate([
            'team_code' => ['required', 'string', 'max:12'],
            'event' => ['nullable', 'in:sample,started,completed'],
            'elapsed_ms' => ['required', 'integer', 'min:0', 'max:86400000'],
            'load_grams' => ['required', 'integer', 'min:0', 'max:1000000'],
            'timestamp' => ['nullable', 'date'],
        ]);

        $team = Team::query()->where('code', $data['team_code'])->first();
        if (! $team) {
            return response()->json(['message' => 'Equipe não encontrada.'], 404);
        }
        if ($team->audit_status !== 'approved') {
            return response()->json(['message' => 'A equipe ainda não está aprovada para ensaio.'], 422);
        }

        $event = $data['event'] ?? 'sample';
        $capturedAt = isset($data['timestamp']) ? CarbonImmutable::parse($data['timestamp']) : now();

        DB::transaction(function () use ($team, $data, $event, $capturedAt): void {
            BridgeLoadSample::create([
                'team_id' => $team->id,
                'elapsed_ms' => $data['elapsed_ms'],
                'load_grams' => $data['load_grams'],
                'created_at' => $capturedAt,
            ]);

            if ($event === 'completed') {
                $peakLoad = max((int) $data['load_grams'], (int) ($team->loadSamples()->max('load_grams') ?? 0));
                $team->update([
                    'test_status' => 'completed',
                    'actual_load_grams' => $peakLoad,
                    'tested_at' => $capturedAt,
                ]);
                return;
            }

            $team->update(['test_status' => 'running']);
        });

        return response()->json([
            'accepted' => true,
            'team_code' => $team->code,
            'event' => $event,
        ], 202);
    }
}
