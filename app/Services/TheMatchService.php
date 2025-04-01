<?php

namespace App\Services;

use App\Models\Team;
use App\Models\TheMatch;
use App\Models\Tournament;
use Illuminate\Database\Eloquent\Collection;

class TheMatchService
{
    public function __construct(private StandingService $standingService, private TournamentService $tournamentService)
    {
        //
    }

    public function getRemainingMatches(Tournament $tournament, Team $team): Collection
    {
        return $tournament->matches()
            ->ofTeam($team->id)
            ->unplayed()
            ->get();
    }

    public function play(TheMatch $match, $homeScore, $awayScore): bool
    {
        if ($match->is_played) {
            return false;
        }

        $match->home_score = $homeScore;
        $match->away_score = $awayScore;
        $match->is_played = true;

        $result = $match->save();

        $this->tournamentService->updateTournamentByPlayedMatch($match->tournament, $match);
        $this->standingService->updateMatchStandingsByPlayedMatch($match);

        return $result;
    }
}
