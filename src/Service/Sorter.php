<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\Sort;
use App\Enum\SortDirection;

final class Sorter
{
    /**
     * @template T
     *
     * @param list<T> $items
     * @param array<string, callable(T, T): int> $comparators ascending comparators keyed by sort field
     * @param (callable(T, T): int)|null $tieBreaker
     *
     * @return list<T>
     */
    public function sort(array $items, Sort $sort, array $comparators, ?callable $tieBreaker = null): array
    {
        $comparator = $comparators[$sort->field] ?? null;

        if (null === $comparator) {
            return $items;
        }

        usort($items, static function (object $a, object $b) use ($comparator, $sort, $tieBreaker): int {
            $result = $comparator($a, $b);

            if (0 === $result && null !== $tieBreaker) {
                return $tieBreaker($a, $b);
            }

            return SortDirection::Desc === $sort->direction ? -$result : $result;
        });

        return $items;
    }
}
