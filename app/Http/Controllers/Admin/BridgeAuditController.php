<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BridgeAuditController extends Controller
{
    public function index(): Response
    {
        $teams = Team::query()->with(['members' => fn ($query) => $query->where('is_leader', true)])->where('status', '!=', 'rejected')->latest()->get()->map(fn (Team $team) => $this->teamData($team));

        return Inertia::render('Admin/BridgeAudits/Index', [
            'teams' => $teams->values(),
            'metrics' => [
                'delivered' => $teams->count(),
                'approved' => $teams->where('auditStatus', 'approved')->count(),
                'rejected' => $teams->where('auditStatus', 'rejected')->count(),
                'waiting' => $teams->where('auditStatus', 'waiting')->count(),
            ],
        ]);
    }

    public function show(Team $team): Response
    {
        $team->load(['members' => fn ($query) => $query->where('is_leader', true)]);
        abort_if($team->status === 'rejected', 404);

        return Inertia::render('Admin/BridgeAudits/Show', ['team' => $this->teamData($team)]);
    }

    public function store(Request $request, Team $team): RedirectResponse
    {
        $data = $request->validate([
            'measured_weight_grams' => ['required', 'integer', 'min:1'],
            'declared_load_grams' => ['required', 'integer', 'min:1'],
            'materials_compliant' => ['required', 'boolean'],
            'dimensions_compliant' => ['required', 'boolean'],
            'no_coating' => ['required', 'boolean'],
            'audit_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $approved = $data['measured_weight_grams'] <= 1010
            && $data['materials_compliant']
            && $data['dimensions_compliant']
            && $data['no_coating'];

        $team->update([...$data, 'audit_status' => $approved ? 'approved' : 'rejected', 'audited_at' => now()]);

        return to_route('admin.bridge-audits.index')->with('success', $approved ? 'Ponte aprovada na auditoria.' : 'Ponte reprovada na auditoria.');
    }

    private function teamData(Team $team): array
    {
        return [
            'id' => $team->id, 'code' => $team->code, 'name' => $team->name,
            'leader' => $team->members->first()?->name ?? 'Líder não informado',
            'course' => $team->members->first()?->course ?? '—',
            'auditStatus' => $team->audit_status, 'measuredWeightGrams' => $team->measured_weight_grams,
            'declaredLoadGrams' => $team->declared_load_grams, 'materialsCompliant' => $team->materials_compliant,
            'dimensionsCompliant' => $team->dimensions_compliant, 'noCoating' => $team->no_coating,
            'auditNotes' => $team->audit_notes,
        ];
    }
}
