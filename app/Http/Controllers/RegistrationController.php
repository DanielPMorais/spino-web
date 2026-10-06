<?php

namespace App\Http\Controllers;

use App\Http\Requests\JoinTeamRequest;
use App\Http\Requests\StoreTeamRequest;
use App\Models\Member;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
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
            'emailVerification' => $this->emailVerification(),
        ]);
    }

    public function store(StoreTeamRequest $request): RedirectResponse
    {
        $this->ensureRegistrationIsOpen();

        $registration = DB::transaction(function () use ($request): array {
            $this->availableEmail($request->string('email')->lower()->toString());

            // PostgreSQL does not allow FOR UPDATE on aggregate queries such as count().
            // The registration checks below use aggregate queries, so keep this query unlocked.
            $activeTeams = Team::query()->whereNot('status', 'rejected');
            if ($activeTeams->count() >= config('competition.max_teams')) {
                throw ValidationException::withMessages(['team_name' => 'Todas as vagas do concurso foram preenchidas.']);
            }

            $team = Team::create([
                'name' => $request->string('team_name')->squish()->toString(),
                'code' => $this->newTeamCode(),
                // Campos legados mantidos até uma migração de limpeza do banco.
                'category' => 'general',
                'quota_type' => 'general',
            ]);
            $member = $team->members()->create([
                'name' => $request->string('name')->squish()->toString(),
                'course' => $request->string('course')->squish()->toString(),
                'email' => $request->string('email')->lower()->toString(),
                'is_leader' => true,
            ]);

            return ['member' => $member, 'teamCode' => $team->code];
        });

        $this->sendVerificationCode($registration['member']);
        session(['registration_verification_member_id' => $registration['member']->id]);

        return to_route('registration.create')->with([
            'success' => 'Equipe criada. Confirme seu e-mail acadêmico para concluir sua participação.',
            'teamCode' => $registration['teamCode'],
            'verificationRequired' => true,
            'verificationEmail' => $registration['member']->email,
        ]);
    }

    public function join(JoinTeamRequest $request): RedirectResponse
    {
        $this->ensureRegistrationIsOpen();

        $member = DB::transaction(function () use ($request): Member {
            $this->availableEmail($request->string('email')->lower()->toString());
            $team = Team::query()->lockForUpdate()->where('code', Str::upper($request->string('code')->toString()))->first();

            if (! $team || $team->status === 'rejected') {
                throw ValidationException::withMessages(['code' => 'Código de equipe inválido ou indisponível.']);
            }

            if ($team->members()->count() >= 5) {
                throw ValidationException::withMessages(['code' => 'Esta equipe já possui o máximo de cinco integrantes.']);
            }

            $member = $team->members()->create([
                'name' => $request->string('name')->squish()->toString(),
                'course' => $request->string('course')->squish()->toString(),
                'email' => $request->string('email')->lower()->toString(),
            ]);

            return $member;
        });

        $this->sendVerificationCode($member);
        session(['registration_verification_member_id' => $member->id]);

        return to_route('registration.create')->with([
            'success' => 'Seu ingresso foi registrado. Confirme seu e-mail acadêmico para concluir sua participação.',
            'verificationRequired' => true,
            'verificationEmail' => $member->email,
        ]);
    }

    public function verifyEmail(): RedirectResponse
    {
        $data = request()->validate(['code' => ['required', 'digits:6']]);
        $member = $this->verificationMember();

        if ($member->email_verified_at) {
            return to_route('registration.create')->with('success', 'Este e-mail acadêmico já foi confirmado.');
        }

        if (! $member->email_verification_expires_at?->isFuture()) {
            throw ValidationException::withMessages(['code' => 'Este código expirou. Solicite o envio de um novo código.']);
        }

        if (! Hash::check($data['code'], $member->email_verification_code ?? '')) {
            throw ValidationException::withMessages(['code' => 'Código inválido. Confira o e-mail e tente novamente.']);
        }

        $teamCode = DB::transaction(function () use ($member): ?string {
            $member->update([
                'email_verified_at' => now(),
                'email_verification_code' => null,
                'email_verification_expires_at' => null,
            ]);

            $team = $member->team()->lockForUpdate()->first();
            if ($team && $team->members()->whereNotNull('email_verified_at')->count() >= 2) {
                $team->update(['status' => 'pending']);
            }

            return $member->is_leader && $team ? $team->code : null;
        });

        session()->forget('registration_verification_member_id');

        if ($teamCode) {
            $this->sendTeamCode($member, $teamCode);
        }

        return to_route('registration.create')->with([
            'success' => $teamCode
                ? 'E-mail acadêmico confirmado. Guarde e compartilhe o código da equipe com os demais integrantes.'
                : 'E-mail acadêmico confirmado. A equipe será encaminhada para análise quando tiver ao menos dois integrantes confirmados.',
            'teamCode' => $teamCode,
        ]);
    }

    public function resendEmailVerification(): RedirectResponse
    {
        $member = $this->verificationMember();

        if ($member->email_verified_at) {
            return to_route('registration.create')->with('success', 'Este e-mail acadêmico já foi confirmado.');
        }

        $this->sendVerificationCode($member);

        return to_route('registration.create')->with([
            'success' => 'Enviamos um novo código de confirmação.',
            'verificationRequired' => true,
            'verificationEmail' => $member->email,
        ]);
    }

    private function availableEmail(string $email): void
    {
        if (Member::where('email', $email)->exists()) {
            throw ValidationException::withMessages(['email' => 'Este e-mail acadêmico já participa de uma equipe.']);
        }
    }

    private function verificationMember(): Member
    {
        $memberId = session('registration_verification_member_id');
        $member = $memberId ? Member::find($memberId) : null;

        if (! $member) {
            throw ValidationException::withMessages(['code' => 'Inicie a inscrição novamente para confirmar seu e-mail.']);
        }

        return $member;
    }

    private function emailVerification(): ?array
    {
        $memberId = session('registration_verification_member_id');
        $member = $memberId ? Member::find($memberId) : null;

        if (! $member || $member->email_verified_at) {
            return null;
        }

        return ['email' => $member->email];
    }

    private function sendVerificationCode(Member $member): void
    {
        $code = (string) random_int(100000, 999999);

        $member->update([
            'email_verification_code' => Hash::make($code),
            'email_verification_expires_at' => now()->addMinutes(15),
        ]);

        Mail::raw(
            "Olá, {$member->name}!\n\nSeu código para confirmar o e-mail acadêmico na Competição de Pontes de Palito 2026 é: {$code}\n\nO código expira em 15 minutos. Se você não iniciou esta inscrição, ignore esta mensagem.",
            function ($message) use ($member): void {
                $message->to($member->email)->subject('Código de confirmação — Competição de Pontes de Palito 2026');
            },
        );
    }

    private function sendTeamCode(Member $member, string $teamCode): void
    {
        Mail::raw(
            "Olá, {$member->name}!\n\nSeu e-mail acadêmico foi confirmado e sua equipe foi criada.\n\nCódigo da equipe: {$teamCode}\n\nCompartilhe este código com os demais integrantes para que eles possam entrar na equipe e confirmar seus próprios e-mails.",
            function ($message) use ($member): void {
                $message->to($member->email)->subject('Código da equipe — Competição de Pontes de Palito 2026');
            },
        );
    }

    private function availability(): array
    {
        $teams = Team::whereNot('status', 'rejected');
        $total = (clone $teams)->count();
        return [
            'isOpen' => $this->isRegistrationOpen(),
            'totalRemaining' => max(0, config('competition.max_teams') - $total),
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
