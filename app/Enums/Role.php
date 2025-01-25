<?php

namespace App\Enums;

enum Role: int
{
    case ADMIN = 1;
    case VIEWER = 2;
    case ENCODER = 3;
    case PRESIDENT = 4;
    case WINGBANDER = 5;
    case RND = 6;
    case FARMOWNER = 7;

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'admin',
            self::VIEWER => 'viewer',
            self::ENCODER => 'encoder',
            self::PRESIDENT => 'president',
            self::WINGBANDER => 'wingbander',
            self::RND => 'rnd',
            self::FARMOWNER => 'farm_owner',
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