<?php

namespace App\Http\Controllers;

use App\Models\JudgeVote;
use App\Models\Team;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class AudienceDashboardController extends Controller
{
    public function index(): Response
    {
        $votes = JudgeVote::all();
        $judgeCount = $votes->count();
        $aestheticTotals = [];
        foreach ($votes as $vote) {
            $aestheticTotals[$vote->first_team_id] = ($aestheticTotals[$vote->first_team_id] ?? 0) + 15;
            $aestheticTotals[$vote->second_team_id] = ($aestheticTotals[$vote->second_team_id] ?? 0) + 10;
            $aestheticTotals[$vote->third_team_id] = ($aestheticTotals[$vote->third_team_id] ?? 0) + 5;
        }

        $teams = Team::where('audit_status', 'approved')->with(['members', 'loadSamples'])->get();
        $ranking = $teams->map(function (Team $team) use ($aestheticTotals, $judgeCount): array {
            $aesthetic = $judgeCount ? round(($aestheticTotals[$team->id] ?? 0) / $judgeCount, 2) : 0;
            $efficiency = (float) ($team->efficiency_score ?? 0);
            $precision = (float) ($team->precision_score ?? 0);
            return [
                'id' => $team->id, 'name' => $team->name, 'category' => $team->quota_type,
                'efficiency' => $efficiency, 'precision' => $precision, 'aesthetic' => $aesthetic,
                'total' => round($efficiency + $precision + $aesthetic, 2),
                'actualLoadKg' => $team->actual_load_grams ? round($team->actual_load_grams / 1000, 2) : null,
                'declaredLoadKg' => $team->declared_load_grams ? round($team->declared_load_grams / 1000, 2) : null,
                'members' => $team->members->map(fn ($member) => ['name' => $member->name, 'course' => $member->course])->values(),
                'ruptureSamples' => $team->loadSamples->sortBy('elapsed_ms')->map(fn ($sample) => [
                    'seconds' => round($sample->elapsed_ms / 1000, 1),
                    'loadKg' => round($sample->load_grams / 1000, 2),
                ])->values(),
                'testedAt' => $team->tested_at?->timestamp ?? PHP_INT_MAX,
            ];
        })->sort(fn ($a, $b) => [$b['total'], $b['precision'], $b['actualLoadKg'] ?? 0, $b['aesthetic'], -$b['testedAt']] <=> [$a['total'], $a['precision'], $a['actualLoadKg'] ?? 0, $a['aesthetic'], -$a['testedAt']])->values()->map(fn ($team, $index) => [...$team, 'position' => $index + 1]);

        $current = Team::where('test_status', 'running')->with('loadSamples')->first();

        return Inertia::render('Public/Dashboard', ['ranking' => $ranking, 'currentTest' => $this->currentTest($current), 'completedJudges' => $judgeCount]);
    }

    private function currentTest(?Team $team): ?array
    {
        if (! $team) return null;
        return ['name' => $team->name, 'category' => $team->quota_type, 'declaredLoadKg' => $team->declared_load_grams ? round($team->declared_load_grams / 1000, 2) : null, 'samples' => $team->loadSamples->sortBy('elapsed_ms')->map(fn ($sample) => ['seconds' => round($sample->elapsed_ms / 1000, 1), 'loadKg' => round($sample->load_grams / 1000, 2)])->values()];
    }
}
