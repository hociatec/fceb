<?php

namespace App\Enum;

enum NavigationItemType: string
{
    case Route = 'route';
    case Page = 'page';
    case Url = 'url';
}
