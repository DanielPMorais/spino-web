<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RegistrationManagementController extends Controller
{
    public function index(): Response
    {
        $teams = Team::query()
            ->whereNot('status', 'forming')
            ->with(['members' => fn ($query) => $query->where('is_leader', true)])
            ->latest()
            ->get()
            ->map(fn (Team $team) => [
                'id' => $team->id,
                'code' => $team->code,
                'name' => $team->name,
                'leader' => $team->members->first()?->name ?? 'Líder não informado',
                'course' => $team->members->first()?->course ?? '—',
                'status' => $team->status,
            ]);

        $activeTeams = $teams->where('status', '!=', 'rejected');

        return Inertia::render('Admin/Registrations/Index', [
            'teams' => $teams->values(),
            'metrics' => [
                'total' => $activeTeams->count(),
                'totalLimit' => config('competition.max_teams'),
                'pending' => $teams->where('status', 'pending')->count(),
                'approved' => $teams->where('status', 'approved')->count(),
            ],
        ]);
    }

    public function updateStatus(Request $request, Team $team): RedirectResponse
    {
        abort_if($team->status === 'forming', 404);

        $validated = $request->validate([
            'status' => ['required', 'in:pending,approved,rejected'],
        ]);

        $team->update($validated);

        return back()->with('success', 'Status da equipe atualizado.');
    }
}
