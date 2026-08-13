<?php

namespace Database\Seeders;

use App\Models\CampusStudent;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        CampusStudent::upsert([
            ['enrollment' => 'CT300001', 'name' => 'Ana Souza', 'course' => 'Engenharia Civil', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['enrollment' => 'CT300002', 'name' => 'Bruno Lima', 'course' => 'Engenharia Civil', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['enrollment' => 'CT300003', 'name' => 'Carla Mendes', 'course' => 'Tecnologia em Processos Gerenciais', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ], ['enrollment'], ['name', 'course', 'is_active', 'updated_at']);

        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
