<?php

declare(strict_types=1);

namespace App\Dto;

use App\Enum\SortDirection;

final class Sort
{
    public function __construct(
        public readonly string $field,
        public readonly SortDirection $direction = SortDirection::Asc,
    ) {
        //
    }
}
