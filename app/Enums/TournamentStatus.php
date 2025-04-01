<?php

namespace App\Enums;

enum TournamentStatus: string
{
    case NotStarted = 'not_started';
    case InProgress = 'in_progress';
    case Finished = 'finished';
}
