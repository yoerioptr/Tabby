<?php

declare(strict_types=1);

namespace App\Enum;

enum SortDirection: string
{
    case Asc = 'asc';
    case Desc = 'desc';

    public static function default(): self
    {
        return self::Asc;
    }

    public static function fromQuery(?string $value): self
    {
        return self::tryFrom((string) $value) ?? self::default();
    }
}
