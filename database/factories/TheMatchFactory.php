<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TheMatch>
 */
class TheMatchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tournament_id' => \App\Models\Tournament::factory(),
            'home_team_id' => \App\Models\Team::factory(),
            'away_team_id' => \App\Models\Team::factory(),
            'week' => $this->faker->numberBetween(1, 6),
        ];
    }
}
