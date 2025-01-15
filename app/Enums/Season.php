<?php

namespace App\Enums;

enum Season: int
{
    case EARLY_BIRD = 1;
    case LOCAL = 2;
    case NATIONAL = 3;
    case LATE_BORN = 4;

    public function label(): string
    {
        return match ($this) {
            self::EARLY_BIRD => 'early_bird',
            self::LOCAL => 'local',
            self::NATIONAL => 'national',
            self::LATE_BORN => 'late_born',
        };
    }

    public static function fromLabel(string $label): ?self
    {
        foreach (self::cases() as $case) {
            if ($case->label() === $label) {
                return $case;
            }
        }

        return null;
    }
}