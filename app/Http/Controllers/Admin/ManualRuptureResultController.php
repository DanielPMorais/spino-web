<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ManualRuptureResultController extends Controller
{
    public function index(): Response
    {
        $teams = Team::query()
            ->where('audit_status', 'approved')
            ->with(['members' => fn ($query) => $query->where('is_leader', true)])
            ->orderBy('name')
            ->get()
            ->map(fn (Team $team) => [
                'id' => $team->id,
                'code' => $team->code,
                'name' => $team->name,
                'leader' => $team->members->first()?->name ?? 'Líder não informado',
                'declaredLoadGrams' => $team->declared_load_grams,
                'sensorLoadGrams' => $team->actual_load_grams,
                'manualLoadGrams' => $team->manual_rupture_load_grams,
                'manualNotes' => $team->manual_rupture_notes,
                'manualRecordedAt' => $team->manual_rupture_recorded_at?->toIso8601String(),
                'effectiveLoadGrams' => $team->actual_load_grams ?? $team->manual_rupture_load_grams,
                'testStatus' => $team->test_status,
            ])->values();

        return Inertia::render('Admin/ManualRuptureResults/Index', ['teams' => $teams]);
    }

    public function store(Request $request, Team $team): RedirectResponse
    {
        abort_unless($team->audit_status === 'approved', 422, 'A ponte precisa estar aprovada na auditoria antes do lançamento do resultado.');

        $data = $request->validate([
            'manual_rupture_load_grams' => ['required', 'integer', 'min:1', 'max:1000000'],
            'manual_rupture_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $team->update([
            ...$data,
            'manual_rupture_recorded_at' => now(),
            'manual_rupture_recorded_by' => $request->user()->id,
            // O valor do sensor, se existir, continua preservado e tem precedência no placar.
            'test_status' => 'completed',
            'tested_at' => $team->tested_at ?? now(),
        ]);

        return to_route('admin.manual-rupture-results.index')
            ->with('success', 'Peso de ruptura manual registrado como contingência.');
    }
}
