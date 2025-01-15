<?php

namespace App\Enums;

enum Role: int
{
    case ADMIN = 1;
    case VIEWER = 2;
    case ENCODER = 3;

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'admin',
            self::VIEWER => 'viewer',
            self::ENCODER => 'encoder',
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