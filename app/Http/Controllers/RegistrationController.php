<?php

namespace App\Http\Controllers;

use App\Http\Requests\JoinTeamRequest;
use App\Http\Requests\StoreTeamRequest;
use App\Models\CampusStudent;
use App\Models\Member;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegistrationController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Registration/Create', [
            'availability' => $this->availability(),
            'registrationEndsAt' => config('competition.registration_ends_at'),
        ]);
    }

    public function student(string $enrollment): JsonResponse
    {
        $student = CampusStudent::query()
            ->where('enrollment', $enrollment)
            ->where('is_active', true)
            ->first();

        if (! $student) {
            return response()->json(['message' => 'Matrícula não encontrada ou inativa no Campus.'], 404);
        }

        if (Member::where('enrollment', $enrollment)->exists()) {
            return response()->json(['message' => 'Esta matrícula já participa de uma equipe.'], 422);
        }

        return response()->json($student->only(['enrollment', 'name', 'course']));
    }

    public function store(StoreTeamRequest $request): RedirectResponse
    {
        $this->ensureRegistrationIsOpen();
        $teamCode = null;

        DB::transaction(function () use ($request, &$teamCode): void {
            $student = $this->availableStudent($request->string('enrollment')->toString());

            // PostgreSQL does not allow FOR UPDATE on aggregate queries such as count().
            // The registration checks below use aggregate queries, so keep this query unlocked.
            $activeTeams = Team::query()->whereNot('status', 'rejected');
            if ($activeTeams->count() >= config('competition.max_teams')) {
                throw ValidationException::withMessages(['team_name' => 'Todas as vagas do concurso foram preenchidas.']);
            }

            $quotaType = $request->string('category')->toString();
            if ($quotaType === 'civil' && $student->course !== config('competition.civil_engineering_course')) {
                throw ValidationException::withMessages(['category' => 'A categoria de Engenharia Civil exige um líder matriculado no curso.']);
            }
            if ($quotaType === 'civil' && (clone $activeTeams)->where('quota_type', 'civil')->count() >= config('competition.civil_engineering_quota')) {
                throw ValidationException::withMessages(['team_name' => 'As vagas reservadas para Engenharia Civil foram preenchidas.']);
            }

            if ($quotaType === 'general' && (clone $activeTeams)->where('quota_type', 'general')->count() >= config('competition.max_teams') - config('competition.civil_engineering_quota')) {
                throw ValidationException::withMessages(['team_name' => 'As vagas de ampla concorrência foram preenchidas.']);
            }

            $team = Team::create([
                'name' => $request->string('team_name')->squish()->toString(),
                'code' => $this->newTeamCode(),
                'category' => $quotaType,
                'quota_type' => $quotaType,
            ]);
            $teamCode = $team->code;

            $team->members()->create([
                'enrollment' => $student->enrollment,
                'name' => $student->name,
                'course' => $student->course,
                'email' => $request->string('email')->lower()->toString(),
                'is_leader' => true,
            ]);
        });

        return to_route('registration.create')->with([
            'success' => 'Equipe criada. Compartilhe o código com os demais integrantes.',
            'teamCode' => $teamCode,
        ]);
    }

    public function join(JoinTeamRequest $request): RedirectResponse
    {
        $this->ensureRegistrationIsOpen();

        DB::transaction(function () use ($request): void {
            $student = $this->availableStudent($request->string('enrollment')->toString());
            $team = Team::query()->lockForUpdate()->where('code', Str::upper($request->string('code')->toString()))->first();

            if (! $team || $team->status === 'rejected') {
                throw ValidationException::withMessages(['code' => 'Código de equipe inválido ou indisponível.']);
            }

            if ($request->string('course')->toString() !== $student->course) {
                throw ValidationException::withMessages(['course' => 'O curso informado não corresponde à base oficial do Campus.']);
            }

            if ($team->members()->count() >= 5) {
                throw ValidationException::withMessages(['code' => 'Esta equipe já possui o máximo de cinco integrantes.']);
            }

            $team->members()->create([
                'enrollment' => $student->enrollment,
                'name' => $student->name,
                'course' => $student->course,
                'email' => $request->string('email')->lower()->toString(),
            ]);

            if ($team->members()->count() >= 2) {
                $team->update(['status' => 'pending']);
            }
        });

        return to_route('registration.create')->with('success', 'Você entrou na equipe com sucesso.');
    }

    private function availableStudent(string $enrollment): CampusStudent
    {
        if (Member::where('enrollment', $enrollment)->exists()) {
            throw ValidationException::withMessages(['enrollment' => 'Esta matrícula já participa de uma equipe.']);
        }

        $student = CampusStudent::where('enrollment', $enrollment)->where('is_active', true)->first();
        if (! $student) {
            throw ValidationException::withMessages(['enrollment' => 'Matrícula não encontrada ou inativa no Campus.']);
        }

        return $student;
    }

    private function availability(): array
    {
        $teams = Team::whereNot('status', 'rejected');
        $total = (clone $teams)->count();
        $civil = (clone $teams)->where('quota_type', 'civil')->count();

        return [
            'isOpen' => $this->isRegistrationOpen(),
            'totalRemaining' => max(0, config('competition.max_teams') - $total),
            'civilRemaining' => max(0, config('competition.civil_engineering_quota') - $civil),
            'generalRemaining' => max(0, (config('competition.max_teams') - config('competition.civil_engineering_quota')) - ($total - $civil)),
        ];
    }

    private function ensureRegistrationIsOpen(): void
    {
        if (! $this->isRegistrationOpen()) {
            throw ValidationException::withMessages(['registration' => 'As inscrições não estão abertas neste momento.']);
        }
    }

    private function isRegistrationOpen(): bool
    {
        $startsAt = config('competition.registration_starts_at');
        $endsAt = config('competition.registration_ends_at');

        return (! $startsAt || now()->greaterThanOrEqualTo(Carbon::parse($startsAt)))
            && (! $endsAt || now()->lessThanOrEqualTo(Carbon::parse($endsAt)));
    }

    private function newTeamCode(): string
    {
        do {
            $code = Str::upper(Str::random(8));
        } while (Team::where('code', $code)->exists());

        return $code;
    }
}
