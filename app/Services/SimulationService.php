<?php

namespace App\Services;

use App\Models\TheMatch;
use App\Models\Tournament;
use App\Enums\TournamentStatus;

class SimulationService
{
    const HOME_ADVANTAGE = 10;

    const RANDOM_FACTOR = 15;

    public function __construct(private TheMatchService $matchService)
    {
        //
    }

    public function playAllWeeks(Tournament $tournament)
    {
        $weeksLeft = $tournament->number_of_weeks - $tournament->last_played_week;

        for ($i = 0; $i < $weeksLeft; $i++) {
            $this->playNextWeek($tournament);
        }
    }

    public function playNextWeek(Tournament $tournament)
    {
        if ($tournament->status !== TournamentStatus::InProgress) {
            throw new \Exception('Tournament is not playable');
        }

        $nextWeeksMatches = $tournament->matches()
            ->where('is_played', false)
            ->where('week', $tournament->fresh()->last_played_week + 1)
            ->get();

        foreach ($nextWeeksMatches as $match) {
            $this->simulateMatch($match);
        }
    }

    public function resetData(Tournament $tournament): bool
    {
        foreach ($tournament->matches as $match) {
            $match->home_score = null;
            $match->away_score = null;
            $match->is_played = false;

            $match->save();
        }

        foreach ($tournament->standings as $standing) {
            $standing->played = 0;
            $standing->won = 0;
            $standing->drawn = 0;
            $standing->lost = 0;
            $standing->goals_for = 0;
            $standing->goal_difference = 0;
            $standing->goals_against = 0;
            $standing->points = 0;

            $standing->save();
        }

        $tournament->last_played_week = 0;
        $tournament->status = TournamentStatus::InProgress;

        return $tournament->save();
    }

    protected function simulateMatch(TheMatch $match): bool
    {
        $homeTeam = $match->homeTeam;
        $awayTeam = $match->awayTeam;

        $homeStrength = min(100, max(0, $homeTeam->strength + self::HOME_ADVANTAGE + mt_rand(-self::RANDOM_FACTOR, self::RANDOM_FACTOR)));
        $awayStrength = min(100, max(0, $awayTeam->strength + mt_rand(-self::RANDOM_FACTOR, self::RANDOM_FACTOR)));

        $homeGoals = $this->calculateGoals($homeStrength);
        $awayGoals = $this->calculateGoals($awayStrength);

        return $this->matchService->play($match, $homeGoals, $awayGoals);
    }

    /**
     * Calculate the number of goals scored by a team based on its strength.
     *
     * This uses a Poisson distribution to simulate the number of goals.
     * See https://en.wikipedia.org/wiki/Poisson_distribution for more details.
     *
     * @param  mixed  $strength
     * @return mixed
     */
    private function calculateGoals($strength)
    {
        // average goal range from 0.5 to 2.5 based on strength
        $averageGoals = 0.5 + ($strength / 100 * 2);

        $L = exp(-$averageGoals); // e^(-lambda), (for example, 0.22313016014843 for lambda = 1.5;  0.030197383422319 for lambda = 3.5)
        $p = 1.0;
        $k = 0;

        do {
            $k++;
            $u = mt_rand() / mt_getrandmax();
            $p *= $u;
        } while ($p > $L);

        return $k - 1;
    }
}
