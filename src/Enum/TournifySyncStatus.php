<?php

namespace App\Enum;

enum TournifySyncStatus: string
{
    case Preview = 'preview';
    case Success = 'success';
    case Failure = 'failure';
}
