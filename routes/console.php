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

    $driver = DB::getDriverName();

    if ($driver === 'sqlite') {
        $tables = collect(DB::select("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT IN ('migrations', 'sqlite_sequence') AND name NOT LIKE 'sqlite_%'"))
            ->pluck('name');

        if ($tables->isEmpty()) {
            $this->info('Nenhuma tabela de dados encontrada para limpar.');

            return Command::SUCCESS;
        }

        DB::statement('PRAGMA foreign_keys = OFF');

        try {
            foreach ($tables as $table) {
                DB::table($table)->delete();
            }

            $hasSequenceTable = collect(DB::select("SELECT name FROM sqlite_master WHERE type = 'table' AND name = 'sqlite_sequence'"))->isNotEmpty();

            if ($hasSequenceTable) {
                DB::table('sqlite_sequence')->whereIn('name', $tables)->delete();
            }
        } finally {
            DB::statement('PRAGMA foreign_keys = ON');
        }

        $this->info('Dados de teste removidos. A tabela migrations foi preservada.');

        return Command::SUCCESS;
    }

    if ($driver === 'pgsql') {
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
    }

    $this->error("Banco {$driver} não é suportado por este comando.");

    return Command::FAILURE;
})->purpose('Remove todos os dados de aplicação, preservando a tabela migrations');

Artisan::command('competition:bootstrap-admin {--force : Confirma a criação ou promoção do administrador inicial}', function (): int {
    if (! $this->option('force')) {
        $this->error('Operação cancelada. Execute novamente com --force para confirmar.');

        return Command::FAILURE;
    }

    $email = mb_strtolower(trim((string) config('competition.bootstrap_admin.email')));
    $name = trim((string) config('competition.bootstrap_admin.name'));
    $password = (string) config('competition.bootstrap_admin.password');

    if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $this->error('BOOTSTRAP_ADMIN_EMAIL deve conter um e-mail válido.');

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
            'role' => 'admin',
        ],
    );

    $user->forceFill([
        'role' => 'admin',
        'email_verified_at' => $user->email_verified_at ?? now(),
    ])->save();

    if (! $user->wasRecentlyCreated) {
        $this->warn('A conta já existia. A senha atual foi preservada e a conta foi promovida a administrador.');

        return Command::SUCCESS;
    }

    $this->info("Conta administrativa criada para {$email}.");

    return Command::SUCCESS;
})->purpose('Cria ou promove a conta administrativa inicial a partir das variáveis de ambiente');
