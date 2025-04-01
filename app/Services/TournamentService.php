<?php

namespace App\Services;

use App\Models\TheMatch;
use App\Models\Tournament;
use App\Enums\TournamentStatus;

class TournamentService
{
    public function updateTournamentByPlayedMatch(Tournament $tournament, TheMatch $match): bool
    {
        if (! $match->is_played) {
            return false;
        }

        $tournament->last_played_week = max($tournament->last_played_week, $match->week);

        if ($tournament->last_played_week >= $tournament->number_of_weeks) {
            $tournament->status = TournamentStatus::Finished;
        }

        return $tournament->save();
    }
}
