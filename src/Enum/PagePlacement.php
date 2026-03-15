<?php

namespace App\Enum;

enum PagePlacement: string
{
    case None = 'none';
    case Header = 'header';
    case Footer = 'footer';
    case Both = 'both';
}
