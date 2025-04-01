<?php

namespace App\Services;

use App\Models\Standing;
use App\Models\TheMatch;
use App\Models\Tournament;
use App\Enums\TournamentStatus;
use Exception;

class StandingService
{
    public function createStandings(Tournament $tournament)
    {
        if ($tournament->teams()->count() < 2) {
            throw new Exception('Tournament must have at least 2 teams.');
        }

        if ($tournament->matches()->count() == 0) {
            throw new Exception('Tournament fixtures are not generated.');
        }

        foreach ($tournament->teams as $team) {
            Standing::create([
                'tournament_id' => $tournament->id,
                'team_id' => $team->id,
            ]);
        }

        $tournament->status = TournamentStatus::InProgress;
        $tournament->save();
    }

    public function updateMatchStandingsByPlayedMatch(TheMatch $match)
    {
        $homeStanding = $match->homeStanding;
        $awayStanding = $match->awayStanding;

        if ($homeStanding === null || $awayStanding === null) {
            throw new \Exception('Standings not found for the match.');
        }

        $homeStanding->played += 1;
        $homeStanding->goals_for += $match->home_score;
        $homeStanding->goals_against += $match->away_score;
        $homeStanding->goal_difference = $match->home_score - $match->away_score;

        $awayStanding->played += 1;
        $awayStanding->goals_for += $match->away_score;
        $awayStanding->goals_against += $match->home_score;
        $awayStanding->goal_difference = $match->away_score - $match->home_score;

        if ($match->home_score > $match->away_score) {
            // home team wins
            $homeStanding->won += 1;
            $homeStanding->points += 3;
            $awayStanding->lost += 1;
        } elseif ($match->home_score < $match->away_score) {
            // away team wins
            $homeStanding->lost += 1;
            $awayStanding->won += 1;
            $awayStanding->points += 3;
        } else {
            // draw
            $homeStanding->drawn += 1;
            $homeStanding->points += 1;
            $awayStanding->drawn += 1;
            $awayStanding->points += 1;
        }

        $homeStanding->save();
        $awayStanding->save();
    }
}
