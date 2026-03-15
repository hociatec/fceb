<?php

namespace App\Enum;

enum MatchStatus: string
{
    case Scheduled = 'scheduled';
    case Completed = 'completed';
    case Postponed = 'postponed';
    case Cancelled = 'cancelled';
}
