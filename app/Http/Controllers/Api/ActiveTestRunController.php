<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TestDevice;
use App\Models\TestRun;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActiveTestRunController extends Controller
{
    public function __invoke(Request $request, string $deviceCode): JsonResponse
    {
        $expectedToken = (string) config('sensors.token');

        if ($expectedToken === '' || ! hash_equals($expectedToken, (string) $request->bearerToken())) {
            return response()->json(['message' => 'Não autorizado.'], 401);
        }

        $device = TestDevice::query()
            ->where('code', strtoupper($deviceCode))
            ->first();

        if (! $device) {
            return response()->json(['message' => 'Dispositivo não cadastrado para o evento.'], 404);
        }

        $device->update(['last_seen_at' => now()]);

        $run = TestRun::query()
            ->with('team')
            ->where('test_device_id', $device->id)
            ->whereIn('status', ['armed', 'running'])
            ->latest('armed_at')
            ->first();

        if (! $run) {
            return response()->json(['active' => false]);
        }

        return response()->json([
            'active' => true,
            'run_id' => $run->uuid,
            'status' => $run->status,
            'team' => [
                'code' => $run->team->code,
                'name' => $run->team->name,
            ],
        ]);
    }
}
