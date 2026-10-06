<?php

declare(strict_types=1);

namespace App\Service;

/**
 * Compares and orders Belgian table-tennis ranking codes. A higher weight means
 * a stronger ranking: A0 is stronger than B6, a lower digit within a letter is
 * stronger, a "+" suffix marks the strongest ranking and NG (or an empty value)
 * is the weakest of all.
 */
final class RankingScale
{
    public static function weight(string $ranking): int
    {
        $ranking = trim($ranking);

        if ('' === $ranking || 'NG' === strtoupper($ranking)) {
            return -1;
        }

        if (str_contains($ranking, '+')) {
            return PHP_INT_MAX;
        }

        $letter = strtoupper($ranking[0]);
        $number = (int) substr($ranking, 1);

        return (\ord('Z') - \ord($letter)) * 100 - $number;
    }

    /**
     * Returns the distinct, non-empty rankings ordered from strongest to
     * weakest.
     *
     * @param iterable<string|null> $rankings
     *
     * @return list<string>
     */
    public static function sort(iterable $rankings): array
    {
        $unique = [];

        foreach ($rankings as $ranking) {
            $ranking = trim((string) $ranking);

            if ('' !== $ranking) {
                $unique[$ranking] = true;
            }
        }

        $sorted = array_keys($unique);

        usort($sorted, static fn (string $a, string $b): int => self::weight($b) <=> self::weight($a));

        return $sorted;
    }
}
