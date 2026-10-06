<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
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
