<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\Console\Command\Command;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('competition:clear-test-data {--force : Confirma a remoção irreversível dos dados}', function (): int {
    if (! $this->option('force')) {
        $this->error('Operação cancelada. Execute novamente com --force para confirmar.');

        return Command::FAILURE;
    }

    if (DB::getDriverName() !== 'pgsql') {
        $this->error('Este comando foi criado para o PostgreSQL do Render.');

        return Command::FAILURE;
    }

    $tables = collect(DB::select("SELECT tablename FROM pg_tables WHERE schemaname = 'public' AND tablename <> 'migrations'"))
        ->pluck('tablename')
        ->map(fn (string $table) => '"'.str_replace('"', '""', $table).'"')
        ->implode(', ');

    if ($tables === '') {
        $this->info('Nenhuma tabela de dados encontrada para limpar.');

        return Command::SUCCESS;
    }

    DB::statement("TRUNCATE TABLE {$tables} RESTART IDENTITY CASCADE");
    $this->info('Dados de teste removidos. A tabela migrations foi preservada.');

    return Command::SUCCESS;
})->purpose('Remove todos os dados de aplicação do PostgreSQL de produção');

Artisan::command('competition:bootstrap-admin {--force : Confirma a criação do administrador inicial}', function (): int {
    if (! $this->option('force')) {
        $this->error('Operação cancelada. Execute novamente com --force para confirmar.');

        return Command::FAILURE;
    }

    $email = mb_strtolower(trim((string) config('competition.bootstrap_admin.email')));
    $name = trim((string) config('competition.bootstrap_admin.name'));
    $password = (string) config('competition.bootstrap_admin.password');
    $administratorEmails = array_map(
        fn (string $administratorEmail) => mb_strtolower(trim($administratorEmail)),
        config('competition.admin_emails'),
    );

    if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $this->error('BOOTSTRAP_ADMIN_EMAIL deve conter um e-mail válido.');

        return Command::FAILURE;
    }

    if (! in_array($email, $administratorEmails, true)) {
        $this->error('Inclua BOOTSTRAP_ADMIN_EMAIL também em COMPETITION_ADMIN_EMAILS.');

        return Command::FAILURE;
    }

    if (mb_strlen($password) < 12) {
        $this->error('BOOTSTRAP_ADMIN_PASSWORD deve ter pelo menos 12 caracteres.');

        return Command::FAILURE;
    }

    $user = User::query()->firstOrCreate(
        ['email' => $email],
        [
            'name' => $name !== '' ? $name : 'Comissão organizadora',
            'password' => Hash::make($password),
        ],
    );

    if ($user->wasRecentlyCreated) {
        $user->forceFill(['email_verified_at' => now()])->save();
    }

    if (! $user->wasRecentlyCreated) {
        $this->warn('A conta administrativa já existe. A senha atual foi preservada.');

        return Command::SUCCESS;
    }

    $this->info("Conta administrativa criada para {$email}.");

    return Command::SUCCESS;
})->purpose('Cria uma única conta administrativa a partir das variáveis de ambiente');
