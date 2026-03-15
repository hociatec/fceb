<?php

namespace App\Enum;

enum ArticlePlacement: string
{
    case None = 'none';
    case Homepage = 'homepage';
    case CurrentSeason = 'current_season';
    case Archive = 'archive';

    public static function choices(): array
    {
        return array_column(self::cases(), 'value');
    }
}
