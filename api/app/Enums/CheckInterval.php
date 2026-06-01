<?php

namespace App\Enums;

enum CheckInterval: int
{
    case M1 = 1;
    case M2 = 2;
    case M3 = 3;
    case M5 = 5;
    case M10 = 10;
    case M20 = 20;
    case M30 = 30;
    case M60 = 60;

    /**
     * @return list<CheckInterval>
     */
    public static function matchingMinute(int $minute): array
    {
        if ($minute === 0) {
            $minute = 60;
        }

        $intervals = [];

        foreach (self::cases() as $interval) {
            if ($minute % $interval->value === 0) {
                $intervals[] = $interval;
            }
        }

        return $intervals;
    }
}
