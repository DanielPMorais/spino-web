<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompetitionSetting;
use App\Models\Judge;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class JudgeManagementController extends Controller
{
    public function index(): Response
    {
        $settings = CompetitionSetting::firstOrCreate([], ['voting_open' => false]);
        return Inertia::render('Admin/Judges/Index', ['judges' => Judge::latest()->get(), 'votingOpen' => $settings->voting_open]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255'], 'email' => ['required', 'email', 'max:255', 'unique:judges,email']]);
        Judge::create([...$data, 'credential' => $this->newCredential()]);
        return back()->with('success', 'Juiz cadastrado com sucesso.');
    }

    public function toggleVoting(): RedirectResponse
    {
        $settings = CompetitionSetting::firstOrCreate([], ['voting_open' => false]);
        $settings->update(['voting_open' => ! $settings->voting_open]);
        return back()->with('success', $settings->voting_open ? 'Janela de votação aberta.' : 'Janela de votação fechada.');
    }

    private function newCredential(): string
    {
        do { $credential = 'JZ-'.Str::upper(Str::random(8)); } while (Judge::where('credential', $credential)->exists());
        return $credential;
    }
}
