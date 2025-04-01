<?php

namespace App\Services;

use App\Models\TheMatch;
use App\Models\Tournament;
use Exception;

class FixtureService
{
    public function generateFixtures(Tournament $tournament): bool
    {
        $teamIds = $tournament->teams->pluck('id')->toArray();
        $teamCount = count($teamIds);

        if ($teamCount < 2) {
            throw new Exception('Tournament must have at least 2 teams.');
        }

        if ($teamCount % 2 !== 0) {
            // bye team
            $teamIds[] = 0;
            $teamCount++;
        }

        $tournament->number_of_weeks = ($teamCount - 1) * 2;

        for ($round = 0; $round < $teamCount - 1; $round++) {
            for ($match = 0; $match < $teamCount / 2; $match++) {
                $homeTeamId = $teamIds[$match];
                $awayTeamId = $teamIds[($teamCount - 1) - $match];

                // skip for bye team
                if ($homeTeamId === 0 || $awayTeamId === 0) {
                    continue;
                }

                TheMatch::create([
                    'tournament_id' => $tournament->id,
                    'week' => $round + 1,
                    'home_team_id' => $homeTeamId,
                    'away_team_id' => $awayTeamId,
                ]);

                TheMatch::create([
                    'tournament_id' => $tournament->id,
                    'week' => $teamCount + $round,
                    'home_team_id' => $awayTeamId,
                    'away_team_id' => $homeTeamId,
                ]);
            }

            // rotate teams
            $second = array_splice($teamIds, 1, 1)[0];
            array_push($teamIds, $second);
        }

        return $tournament->save();
    }
}
