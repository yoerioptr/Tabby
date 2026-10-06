<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\RankingScale;
use PHPUnit\Framework\TestCase;

final class RankingScaleTest extends TestCase
{
    public function testItSortsRankingsFromStrongestToWeakest(): void
    {
        $sorted = RankingScale::sort(['D2', 'D4', 'D0', 'D6', 'NG', 'A0', 'B6', 'A+', 'C3', '', null]);

        self::assertSame(['A+', 'A0', 'B6', 'C3', 'D0', 'D2', 'D4', 'D6', 'NG'], $sorted);
    }

    public function testItDeduplicatesRankings(): void
    {
        self::assertSame(['D2', 'D4'], RankingScale::sort(['D2', 'D4', 'D2']));
    }

    public function testItWeightsRankings(): void
    {
        self::assertGreaterThan(RankingScale::weight('B6'), RankingScale::weight('A0'));
        self::assertGreaterThan(RankingScale::weight('A6'), RankingScale::weight('A0'));
        self::assertGreaterThan(RankingScale::weight('NG'), RankingScale::weight('D6'));
        self::assertSame(-1, RankingScale::weight(''));
    }
}
