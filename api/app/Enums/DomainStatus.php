<?php

namespace App\Enums;

enum DomainStatus: string
{
    case Unknown = 'unknown';
    case Up = 'up';
    case Down = 'down';
}
