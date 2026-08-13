<?php

namespace App\Http\Controllers;

use App\Models\CompetitionSetting;
use App\Models\Judge;
use App\Models\JudgeVote;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class JudgePanelController extends Controller
{
    public function show(string $credential): Response
    {
        $judge = Judge::where('credential', $credential)->firstOrFail();
        $judge->load(['vote.firstTeam', 'vote.secondTeam', 'vote.thirdTeam']);

        return Inertia::render('Judge/Panel', [
            'judge' => ['name' => $judge->name, 'credential' => $judge->credential, 'voteStatus' => $judge->vote_status],
            'votingOpen' => CompetitionSetting::first()?->voting_open ?? false,
            'teams' => Team::where('audit_status', 'approved')->orderBy('name')->get(['id', 'name']),
            'vote' => $judge->vote ? [
                'first' => $judge->vote->firstTeam->name,
                'second' => $judge->vote->secondTeam->name,
                'third' => $judge->vote->thirdTeam->name,
            ] : null,
        ]);
    }

    public function store(Request $request, string $credential): RedirectResponse
    {
        if (! (CompetitionSetting::first()?->voting_open ?? false)) {
            throw ValidationException::withMessages(['voting' => 'A janela de votação está fechada.']);
        }

        $eligibleTeam = Rule::exists('teams', 'id')->where('audit_status', 'approved');
        $data = $request->validate([
            'first_team_id' => ['required', 'integer', $eligibleTeam],
            'second_team_id' => ['required', 'integer', 'different:first_team_id', $eligibleTeam],
            'third_team_id' => ['required', 'integer', 'different:first_team_id', 'different:second_team_id', $eligibleTeam],
        ]);

        DB::transaction(function () use ($credential, $data): void {
            $judge = Judge::where('credential', $credential)->lockForUpdate()->firstOrFail();
            if ($judge->vote()->exists()) {
                throw ValidationException::withMessages(['voting' => 'Seu voto único já foi registrado.']);
            }
            JudgeVote::create([...$data, 'judge_id' => $judge->id]);
            $judge->update(['vote_status' => 'completed']);
        });

        return back();
    }
}
