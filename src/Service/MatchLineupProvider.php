<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\CompetitionMatch;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Yoerioptr\TabtApiClient\Entries\TeamMatchDetailsEntry;
use Yoerioptr\TabtApiClient\Entries\TeamMatchPlayerEntry;
use Yoerioptr\TabtApiClient\Entries\TeamMatchPlayerList;
use Yoerioptr\TabtApiClient\Entries\TeamMatchesEntry;
use Yoerioptr\TabtApiClient\TabtInterface;

/**
 * Resolves the players and individual game results for a match. Played matches
 * expose this through the TabT "WithDetails" match response; planned line-ups
 * are not available yet, so placeholder names are returned for matches without
 * details.
 */
final class MatchLineupProvider
{
    /**
     * @var list<array{firstName: string, lastName: string, ranking: string}>
     */
    private const array PLANNED_PLAYERS = [
        ['firstName' => 'John', 'lastName' => 'Doe', 'ranking' => 'D2'],
        ['firstName' => 'Arnold', 'lastName' => 'Schwarzenegger', 'ranking' => 'D4'],
        ['firstName' => 'Sylvester', 'lastName' => 'Stallone', 'ranking' => 'D0'],
        ['firstName' => 'Jean-Claude', 'lastName' => 'Van Damme', 'ranking' => 'E0'],
    ];

    /**
     * @var array<string, TeamMatchDetailsEntry|null>
     */
    private array $detailsCache = [];

    public function __construct(
        private readonly TabtInterface $tabt,
        #[Autowire('%env(TABT_CLUB)%')]
        private readonly string $clubId = '',
    ) {
    }

    /**
     * @return array{
     *     home: list<array{firstName: string, lastName: string, ranking: string}>,
     *     away: list<array{firstName: string, lastName: string, ranking: string}>
     * }
     */
    public function forMatch(CompetitionMatch $match): array
    {
        $details = $this->details($match);

        if (null !== $details) {
            return [
                'home' => $this->extractPlayers($details->getHomePlayers()),
                'away' => $this->extractPlayers($details->getAwayPlayers()),
            ];
        }

        $half = (int) ceil(count(self::PLANNED_PLAYERS) / 2);

        return [
            'home' => array_slice(self::PLANNED_PLAYERS, 0, $half),
            'away' => array_slice(self::PLANNED_PLAYERS, $half),
        ];
    }

    /**
     * @return list<array{
     *     position: int,
     *     home: string,
     *     away: string,
     *     homeSets: int,
     *     awaySets: int
     * }>
     */
    public function results(CompetitionMatch $match): array
    {
        $details = $this->details($match);

        if (null === $details) {
            return [];
        }

        [$homeByUnique, $homeByPosition] = $this->playerLookup($details->getHomePlayers());
        [$awayByUnique, $awayByPosition] = $this->playerLookup($details->getAwayPlayers());

        $rows = [];

        foreach ($details->getIndividualMatchResults() as $result) {
            $rows[] = [
                'position' => $result->getPosition() ?? 0,
                'home' => $this->resolvePlayers(
                    $result->getHomePlayerUniqueIndex(),
                    $result->getHomePlayerMatchIndex(),
                    $homeByUnique,
                    $homeByPosition,
                ),
                'away' => $this->resolvePlayers(
                    $result->getAwayPlayerUniqueIndex(),
                    $result->getAwayPlayerMatchIndex(),
                    $awayByUnique,
                    $awayByPosition,
                ),
                'homeSets' => $result->getHomeSetCount() ?? 0,
                'awaySets' => $result->getAwaySetCount() ?? 0,
            ];
        }

        usort($rows, static fn (array $a, array $b): int => $a['position'] <=> $b['position']);

        return $rows;
    }

    private function details(CompetitionMatch $match): ?TeamMatchDetailsEntry
    {
        $parameters = [
            'Club' => $this->clubId,
            'WithDetails' => true,
        ];

        if (null !== $match->getMatchUniqueId()) {
            $key = 'unique:'.$match->getMatchUniqueId();
            $parameters['MatchUniqueId'] = $match->getMatchUniqueId();
        } elseif (null !== $match->getMatchId()) {
            $key = 'id:'.$match->getMatchId();
            $parameters['MatchId'] = $match->getMatchId();
        } else {
            return null;
        }

        if (array_key_exists($key, $this->detailsCache)) {
            return $this->detailsCache[$key];
        }

        $details = null;

        foreach ($this->tabt->match()->listMatchesBy($parameters)->getTeamMatchesEntries() as $entry) {
            if (!$this->isSameMatch($entry, $match)) {
                continue;
            }

            $details = $entry->getMatchDetails();

            break;
        }

        return $this->detailsCache[$key] = $details;
    }

    private function isSameMatch(TeamMatchesEntry $entry, CompetitionMatch $match): bool
    {
        try {
            if (null !== $match->getMatchUniqueId()) {
                return $entry->getMatchUniqueId() === $match->getMatchUniqueId();
            }

            return $entry->getMatchId() === $match->getMatchId();
        } catch (\Error) {
            return false;
        }
    }

    /**
     * @return list<array{firstName: string, lastName: string, ranking: string}>
     */
    private function extractPlayers(?TeamMatchPlayerList $list): array
    {
        if (null === $list) {
            return [];
        }

        $players = [];

        foreach ($list->getPlayers() as $player) {
            $players[] = [
                'firstName' => $player->getFirstName(),
                'lastName' => $player->getLastName(),
                'ranking' => $this->playerRanking($player),
            ];
        }

        return $players;
    }

    private function playerRanking(TeamMatchPlayerEntry $player): string
    {
        try {
            return $player->getRanking();
        } catch (\Error) {
            return '';
        }
    }

    /**
     * @return array{
     *     array<int, string>,
     *     array<int, string>
     * }
     */
    private function playerLookup(?TeamMatchPlayerList $list): array
    {
        $byUniqueIndex = [];
        $byPosition = [];

        if (null === $list) {
            return [$byUniqueIndex, $byPosition];
        }

        foreach ($list->getPlayers() as $player) {
            $name = trim($player->getFirstName().' '.$player->getLastName());
            $ranking = $this->playerRanking($player);

            if ('' !== $ranking) {
                $name = sprintf('%s (%s)', $name, $ranking);
            }

            try {
                $byUniqueIndex[$player->getUniqueIndex()] = $name;
            } catch (\Error) {
                // Unique index was not initialised.
            }

            $position = $player->getPosition();

            if (null !== $position) {
                $byPosition[$position] = $name;
            }
        }

        return [$byUniqueIndex, $byPosition];
    }

    /**
     * @param int[]               $uniqueIndexes
     * @param int[]               $matchIndexes
     * @param array<int, string>  $byUniqueIndex
     * @param array<int, string>  $byPosition
     */
    private function resolvePlayers(
        array $uniqueIndexes,
        array $matchIndexes,
        array $byUniqueIndex,
        array $byPosition,
    ): string {
        $names = [];

        foreach ($uniqueIndexes as $index => $uniqueIndex) {
            if (isset($byUniqueIndex[$uniqueIndex])) {
                $names[] = $byUniqueIndex[$uniqueIndex];

                continue;
            }

            $matchIndex = $matchIndexes[$index] ?? null;

            if (null !== $matchIndex && isset($byPosition[$matchIndex])) {
                $names[] = $byPosition[$matchIndex];

                continue;
            }

            $names[] = '#'.$uniqueIndex;
        }

        if ([] === $names) {
            foreach ($matchIndexes as $matchIndex) {
                if (isset($byPosition[$matchIndex])) {
                    $names[] = $byPosition[$matchIndex];
                }
            }
        }

        return [] !== $names ? implode(' / ', $names) : '—';
    }
}
