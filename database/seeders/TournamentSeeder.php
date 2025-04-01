<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\Tournament;
use Illuminate\Database\Seeder;

class TournamentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Tournament::factory()
            ->has(
                Team::factory()
                    ->count(4)
                    ->sequence(
                        ['name' => 'Manchester City', 'strength' => 93],
                        ['name' => 'Liverpool', 'strength' => 92],
                        ['name' => 'Arsenal', 'strength' => 69],
                        ['name' => 'Manchester United', 'strength' => 58],
                    ),
                'teams'
            )
            ->create([
                'name' => 'Premier League 2024/25',
            ]);

        Team::factory()
            ->count(4)
            ->sequence(
                ['name' => 'Chelsea', 'strength' => 70],
                ['name' => 'Tottenham', 'strength' => 68],
                ['name' => 'Aston Villa', 'strength' => 66],
                ['name' => 'Newcastle', 'strength' => 65],
            )
            ->create();
    }
}
