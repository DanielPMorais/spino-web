<?php

namespace Database\Seeders;

use App\Models\CampusStudent;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        CampusStudent::upsert([
            ['enrollment' => 'CT300001', 'name' => 'Ana Souza', 'course' => 'Engenharia Civil', 'ira' => 8.50, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['enrollment' => 'CT300002', 'name' => 'Bruno Lima', 'course' => 'Engenharia Civil', 'ira' => 7.75, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['enrollment' => 'CT300003', 'name' => 'Carla Mendes', 'course' => 'Tecnologia em Processos Gerenciais', 'ira' => 8.00, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ], ['enrollment'], ['name', 'course', 'ira', 'is_active', 'updated_at']);

        // User::factory(10)->create();

        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'remember_token' => Str::random(10),
            ],
        );

        $this->call(DemoCompetitionSeeder::class);
    }
}
