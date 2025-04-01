<?php

namespace App\Services;

use App\Models\Team;
use App\Models\Tournament;
use App\Enums\TournamentStatus;
use Illuminate\Support\Collection;

class PredictionService
{
    const MINIMUM_PLAYED_WEEKS_TO_PREDICT = 3;

    public function __construct(private TheMatchService $matchService)
    {
        //
    }

    public function predictChampionshipRates(Tournament $tournament): array
    {
        if (
            $tournament->status !== TournamentStatus::InProgress ||
            $tournament->last_played_week < self::MINIMUM_PLAYED_WEEKS_TO_PREDICT ||
            $tournament->teams->isEmpty()
        ) {
            return [];
        }

        $teamStrengths = $this->calculateTeamStrengths($tournament);

        return $this->calculatePredictionsOfChampionship($tournament->teams, $teamStrengths);
    }

    private function calculateTeamStrengths(Tournament $tournament): array
    {
        $leaderPoints = $tournament->standings()->max('points') ?? 0;
        $teamStrengths = [];

        foreach ($tournament->teams as $team) {
            $mathematicallyPossible = $this->isChampionshipMathematicallyPossible($tournament, $team, $leaderPoints);

            if (! $mathematicallyPossible) {
                $teamStrengths[$team->id] = 0;

                continue;
            }

            $strength = $this->calculateTeamStrength($tournament, $team);

            $teamStrengths[$team->id] = $this->adjustForRemainingFixtures(
                $strength,
                $team,
                $tournament
            );
        }

        return $teamStrengths;
    }

    private function isChampionshipMathematicallyPossible(Tournament $tournament, Team $team, int $leaderPoints): bool
    {
        $standing = $tournament->standings->where('team_id', $team->id)->first();
        $remainingFixturesOfTeam = $this->matchService->getRemainingMatches($tournament, $team);

        $currentPoints = $standing->points ?? 0;
        $remainingPoints = $remainingFixturesOfTeam->count() * 3;
        $maxPossiblePoints = $currentPoints + $remainingPoints;

        return $maxPossiblePoints >= $leaderPoints;
    }

    private function calculateTeamStrength(Tournament $tournament, Team $team): float
    {
        $standing = $tournament->standings->where('team_id', $team->id)->first();

        if (! $standing || $standing->played == 0) {
            return $team->strength;
        }

        $winRate = $standing->won / $standing->played;
        $drawRate = $standing->drawn / $standing->played;
        $pointsPerGame = $standing->points / $standing->played;
        $goalDifference = $standing->goal_difference;

        $tournamentProgress = $standing->played / ($tournament->number_of_weeks * 2);

        $performanceStrength = (
            ($winRate * 60) +
            ($drawRate * 20) +
            ($pointsPerGame * 10) +
            (max(-10, min(10, $goalDifference)) * 0.5)
        );

        $weightOfPerformanceStrength = min(0.8, $tournamentProgress);

        return ($performanceStrength * $weightOfPerformanceStrength) + ($team->strength * (1 - $weightOfPerformanceStrength));
    }

    private function adjustForRemainingFixtures(float $strength, Team $team, Tournament $tournament): float
    {
        $remainingFixtures = $this->matchService->getRemainingMatches($tournament, $team);

        if ($remainingFixtures->isEmpty()) {
            return $strength;
        }

        $totalOpponentStrength = 0;
        $totalOpponentCount = 0;
        $homeMatches = 0;

        foreach ($remainingFixtures as $match) {
            $isHome = $match->home_team_id === $team->id;
            $opponentId = $isHome ? $match->away_team_id : $match->home_team_id;
            $opponent = $tournament->teams->firstWhere('id', $opponentId);

            if ($opponent) {
                $totalOpponentStrength += $opponent->strength;
                $totalOpponentCount++;
            }

            if ($isHome) {
                $homeMatches++;
            }
        }

        $avgOpponentStrength = $totalOpponentStrength / max(1, $totalOpponentCount);

        $avgTeamStrength = $tournament->teams->avg('strength');
        $homeAdvantage = $homeMatches / max(1, $remainingFixtures->count());

        $difficultyFactor = ($avgTeamStrength - $avgOpponentStrength) * 0.1;

        return $strength + $difficultyFactor + $homeAdvantage;
    }

    private function calculatePredictionsOfChampionship(Collection $teams, array $teamStrengths): array
    {
        $predictions = [];
        $totalStrength = array_sum($teamStrengths);

        foreach ($teams as $team) {
            $championshipProbability = 0.0;
            if ($totalStrength > 0) {
                $championshipProbability = $teamStrengths[$team->id] / $totalStrength;
            } else {
                $championshipProbability = 1 / count($teams);
            }

            $predictions[] = [
                'team' => $team,
                'strength_rating' => $teamStrengths[$team->id],
                'championship_probability' => round($championshipProbability * 100, 2),
            ];
        }

        usort($predictions, function ($a, $b) {
            return $b['championship_probability'] <=> $a['championship_probability'];
        });

        return $predictions;
    }
}
