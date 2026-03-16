<?php

namespace App\Enum;

enum ArticleHomepageSlot: string
{
    case None = 'none';
    case Featured = 'featured';
    case Secondary = 'secondary';
}
