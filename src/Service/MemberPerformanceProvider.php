<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Member;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Yoerioptr\TabtApiClient\Entries\IndividualMatchResultEntry;
use Yoerioptr\TabtApiClient\Entries\TeamMatchPlayerList;
use Yoerioptr\TabtApiClient\TabtInterface;

/**
 * Computes a member's playing performance for the current season: their
 * ranking, an (unavailable) elo and a win/loss tally grouped by the ranking of
 * the opponents they faced.
 */
final class MemberPerformanceProvider
{
    /**
     * @var array<int, list<array{ranking: string, won: int, lost: int}>>|null
     */
    private ?array $resultsByMember = null;

    public function __construct(
        private readonly TabtInterface $tabt,
        #[Autowire('%env(TABT_CLUB)%')]
        private readonly string $clubId = '',
    ) {
    }

    /**
     * @return array{
     *     currentRanking: ?string,
     *     currentElo: null,
     *     estimatedRanking: null,
     *     results: list<array{ranking: string, won: int, lost: int}>
     * }
     */
    public function forMember(Member $member): array
    {
        return [
            'currentRanking' => $member->getRanking(),
            'currentElo' => null,
            'estimatedRanking' => null,
            'results' => $this->results()[$member->getId()] ?? [],
        ];
    }

    /**
     * @return array<int, list<array{ranking: string, won: int, lost: int}>>
     */
    private function results(): array
    {
        if (null !== $this->resultsByMember) {
            return $this->resultsByMember;
        }

        /**
         * @var array<int, array<string, array{ranking: string, won: int, lost: int}>> $tally
         */
        $tally = [];

        $response = $this->tabt->match()->listMatchesBy([
            'Club' => $this->clubId,
            'WithDetails' => true,
        ]);

        foreach ($response->getTeamMatchesEntries() as $entry) {
            $details = $entry->getMatchDetails();

            if (null === $details) {
                continue;
            }

            $homeRankings = $this->rankingsByUniqueIndex($details->getHomePlayers());
            $awayRankings = $this->rankingsByUniqueIndex($details->getAwayPlayers());

            foreach ($details->getIndividualMatchResults() as $result) {
                $this->tally($tally, $result, $homeRankings, $awayRankings);
            }
        }

        return $this->resultsByMember = $this->sorted($tally);
    }

    /**
     * @param array<int, array<string, array{ranking: string, won: int, lost: int}>> $tally
     * @param array<int, string>                                                     $homeRankings
     * @param array<int, string>                                                     $awayRankings
     */
    private function tally(
        array &$tally,
        IndividualMatchResultEntry $result,
        array $homeRankings,
        array $awayRankings,
    ): void {
        $homeSets = $result->getHomeSetCount();
        $awaySets = $result->getAwaySetCount();

        if (null === $homeSets || null === $awaySets) {
            return;
        }

        foreach ($result->getHomePlayerUniqueIndex() as $uniqueIndex) {
            $this->add(
                $tally,
                $uniqueIndex,
                $this->strongestRanking($result->getAwayPlayerUniqueIndex(), $awayRankings),
                $homeSets > $awaySets,
            );
        }

        foreach ($result->getAwayPlayerUniqueIndex() as $uniqueIndex) {
            $this->add(
                $tally,
                $uniqueIndex,
                $this->strongestRanking($result->getHomePlayerUniqueIndex(), $homeRankings),
                $awaySets > $homeSets,
            );
        }
    }

    /**
     * @param array<int, array<string, array{ranking: string, won: int, lost: int}>> $tally
     */
    private function add(array &$tally, int $uniqueIndex, ?string $ranking, bool $won): void
    {
        if (null === $ranking || '' === $ranking) {
            return;
        }

        $tally[$uniqueIndex][$ranking] ??= ['ranking' => $ranking, 'won' => 0, 'lost' => 0];

        if ($won) {
            ++$tally[$uniqueIndex][$ranking]['won'];
        } else {
            ++$tally[$uniqueIndex][$ranking]['lost'];
        }
    }

    /**
     * A doubles game has two opponents; the result is counted once under the
     * strongest ranking faced.
     *
     * @param int[]             $uniqueIndexes
     * @param array<int, string> $rankings
     */
    private function strongestRanking(array $uniqueIndexes, array $rankings): ?string
    {
        $strongest = null;
        $strongestWeight = null;

        foreach ($uniqueIndexes as $uniqueIndex) {
            $ranking = $rankings[$uniqueIndex] ?? null;

            if (null === $ranking || '' === $ranking) {
                continue;
            }

            $weight = $this->rankingWeight($ranking);

            if (null === $strongestWeight || $weight > $strongestWeight) {
                $strongest = $ranking;
                $strongestWeight = $weight;
            }
        }

        return $strongest;
    }

    /**
     * @return array<int, string>
     */
    private function rankingsByUniqueIndex(?TeamMatchPlayerList $list): array
    {
        if (null === $list) {
            return [];
        }

        $rankings = [];

        foreach ($list->getPlayers() as $player) {
            try {
                $rankings[$player->getUniqueIndex()] = $player->getRanking();
            } catch (\Error) {
                // The unique index was never initialised by the API response.
            }
        }

        return $rankings;
    }

    /**
     * Orders every member's result rows from the strongest opponent ranking to
     * the weakest.
     *
     * @param array<int, array<string, array{ranking: string, won: int, lost: int}>> $tally
     *
     * @return array<int, list<array{ranking: string, won: int, lost: int}>>
     */
    private function sorted(array $tally): array
    {
        $result = [];

        foreach ($tally as $uniqueIndex => $rankings) {
            $rows = array_values($rankings);

            usort(
                $rows,
                fn (array $a, array $b): int => $this->rankingWeight($b['ranking']) <=> $this->rankingWeight($a['ranking']),
            );

            $result[$uniqueIndex] = $rows;
        }

        return $result;
    }

    /**
     * Higher weight means a stronger ranking. A0 is stronger than B6 and NG is
     * the weakest of all.
     */
    private function rankingWeight(string $ranking): int
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
}
