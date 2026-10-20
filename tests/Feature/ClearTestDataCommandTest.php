<?php

namespace Tests\Feature;

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClearTestDataCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_clears_sqlite_application_data_but_keeps_migrations(): void
    {
        User::factory()->create();
        Team::create(['name' => 'Ponte Mock', 'code' => 'MOCK0001', 'category' => 'general', 'quota_type' => 'general']);

        $this->artisan('competition:clear-test-data --force')
            ->expectsOutput('Dados de teste removidos. A tabela migrations foi preservada.')
            ->assertSuccessful();

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('teams', 0);
        $this->assertDatabaseCount('migrations', 1);
    }
}
