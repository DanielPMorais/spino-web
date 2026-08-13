<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompetitionSetting;
use App\Models\Judge;
use App\Models\Team;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    public function index(): Response
    {
        $teams = Team::query();
        $activeTeams = (clone $teams)->where('status', '!=', 'rejected');
        $pendingRegistrations = (clone $teams)->where('status', 'pending')->count();
        $waitingAudits = (clone $teams)->where('status', '!=', 'rejected')->where('audit_status', 'waiting')->count();
        $approvedAudits = (clone $teams)->where('audit_status', 'approved')->count();
        $judges = Judge::query();
        $completedVotes = (clone $judges)->where('vote_status', 'completed')->count();
        $judgeCount = (clone $judges)->count();
        $votingOpen = CompetitionSetting::first()?->voting_open ?? false;
        $currentTest = (clone $teams)->where('test_status', 'running')->first(['id', 'name']);

        return Inertia::render('Admin/Dashboard', [
            'metrics' => [
                'activeTeams' => $activeTeams->count(),
                'teamLimit' => config('competition.max_teams'),
                'pendingRegistrations' => $pendingRegistrations,
                'waitingAudits' => $waitingAudits,
                'approvedAudits' => $approvedAudits,
                'judges' => $judgeCount,
                'completedVotes' => $completedVotes,
                'votingOpen' => $votingOpen,
            ],
            'currentTest' => $currentTest,
            'alerts' => array_values(array_filter([
                $pendingRegistrations > 0 ? [
                    'tone' => 'warning',
                    'title' => "$pendingRegistrations inscrição(ões) aguardando análise",
                    'description' => 'Revise as equipes antes de encaminhá-las para a organização.',
                    'route' => 'admin.registrations.index',
                    'action' => 'Revisar inscrições',
                ] : null,
                $waitingAudits > 0 ? [
                    'tone' => 'info',
                    'title' => "$waitingAudits ponte(s) aguardando auditoria",
                    'description' => 'Registre peso, carga declarada e conformidade física.',
                    'route' => 'admin.bridge-audits.index',
                    'action' => 'Abrir organização',
                ] : null,
                $approvedAudits < 3 ? [
                    'tone' => 'danger',
                    'title' => 'Ainda não há três pontes aptas para votação',
                    'description' => 'O painel do juiz exige ao menos três pontes aprovadas na auditoria.',
                    'route' => 'admin.bridge-audits.index',
                    'action' => 'Ver auditorias',
                ] : null,
                $votingOpen ? [
                    'tone' => 'success',
                    'title' => 'Janela de votação aberta',
                    'description' => "$completedVotes de $judgeCount juiz(es) já concluíram o voto.",
                    'route' => 'admin.judges.index',
                    'action' => 'Acompanhar votação',
                ] : null,
            ])),
        ]);
    }
}
