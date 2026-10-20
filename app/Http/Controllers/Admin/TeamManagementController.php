<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TeamManagementController extends Controller
{
    public function index(): Response
    {
        $teams = Team::query()
            ->with('members:id,team_id,name,email,course,is_leader')
            ->orderBy('name')
            ->get()
            ->map(fn (Team $team) => [
                'id' => $team->id,
                'name' => $team->name,
                'code' => $team->code,
                'status' => $team->status,
                'members' => $team->members->map(fn ($member) => [
                    'name' => $member->name,
                    'email' => $member->email,
                    'course' => $member->course,
                    'isLeader' => $member->is_leader,
                ])->values(),
            ]);

        return Inertia::render('Admin/Teams/Index', compact('teams'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:teams,name'],
            'code' => ['nullable', 'string', 'max:12', 'regex:/^[A-Za-z0-9_-]+$/', 'unique:teams,code'],
            'members' => ['required', 'array', 'min:2', 'max:5'],
            'members.*.name' => ['required', 'string', 'max:255'],
            'members.*.email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'distinct', Rule::unique('members', 'email')],
            'members.*.course' => ['required', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($data): void {
            $team = Team::create([
                'name' => str($data['name'])->squish()->toString(),
                'code' => isset($data['code']) && $data['code'] !== ''
                    ? strtoupper($data['code'])
                    : $this->newTeamCode(),
                'category' => 'general',
                'quota_type' => 'general',
                'status' => 'pending',
            ]);

            foreach ($data['members'] as $index => $member) {
                $team->members()->create([
                    'name' => str($member['name'])->squish()->toString(),
                    'email' => mb_strtolower($member['email']),
                    'course' => str($member['course'])->squish()->toString(),
                    'is_leader' => $index === 0,
                    'email_verified_at' => now(),
                ]);
            }
        });

        return to_route('admin.teams.index')->with('success', 'Equipe cadastrada manualmente e encaminhada para análise.');
    }

    public function destroy(Request $request, Team $team): RedirectResponse
    {
        $data = $request->validate([
            'confirmation_code' => ['required', 'string'],
        ]);

        abort_unless(
            hash_equals($team->code, strtoupper(trim($data['confirmation_code']))),
            422,
            'Confirme digitando o código exato da equipe.',
        );

        $team->delete();

        return to_route('admin.teams.index')->with('success', 'Equipe, integrantes e dados vinculados removidos.');
    }

    private function newTeamCode(): string
    {
        do {
            $code = Str::upper(Str::random(8));
        } while (Team::query()->where('code', $code)->exists());

        return $code;
    }
}
