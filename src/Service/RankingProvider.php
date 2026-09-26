<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Yoerioptr\TabtApiClient\Entries\RankingEntry;
use Yoerioptr\TabtApiClient\Response\GetDivisionRankingResponse;
use Yoerioptr\TabtApiClient\TabtInterface;

/**
 * Builds the current-season ranking overview for the configured club. Every
 * division the club plays in is resolved once (even when multiple club teams
 * share a division) and enriched with its ranking table.
 */
final class RankingProvider
{
    public function __construct(
        private readonly TabtInterface $tabt,
        #[Autowire('%env(TABT_CLUB)%')]
        private readonly string $clubId = '',
    ) {
    }

    /**
     * @return list<array{
     *     divisionName: ?string,
     *     entries: list<array{position: int, team: string, points: int, isOwn: bool}>
     * }>
     */
    public function forCurrentSeason(): array
    {
        $divisionNames = [];

        foreach ($this->tabt->club()->listTeamsBy(['Club' => $this->clubId])->getTeamEntries() as $team) {
            $divisionId = $team->getDivisionId();

            if (null === $divisionId || array_key_exists($divisionId, $divisionNames)) {
                continue;
            }

            try {
                $divisionNames[$divisionId] = $team->getDivisionName();
            } catch (\Error) {
                // The API did not include the division name for this team.
                $divisionNames[$divisionId] = null;
            }
        }

        $divisions = [];

        foreach ($divisionNames as $divisionId => $fallbackName) {
            $response = $this->tabt->division()->listDivisionRankingByDivisionId($divisionId);

            $entries = [];

            foreach ($response->getRankingEntries() as $entry) {
                $entries[] = $this->normalize($entry);
            }

            $divisions[] = [
                'divisionName' => $this->divisionName($response, $fallbackName),
                'entries' => $entries,
            ];
        }

        return $divisions;
    }

    /**
     * @return array{position: int, team: string, points: int, isOwn: bool}
     */
    private function normalize(RankingEntry $entry): array
    {
        return [
            'position' => $entry->getPosition(),
            'team' => $entry->getTeam(),
            'points' => $entry->getPoints(),
            'isOwn' => $this->isOwnTeam($entry),
        ];
    }

    private function isOwnTeam(RankingEntry $entry): bool
    {
        if ('' === $this->clubId) {
            return false;
        }

        try {
            return $entry->getTeamClub() === $this->clubId;
        } catch (\Error) {
            // The API did not include the team club for this entry.
            return false;
        }
    }

    private function divisionName(GetDivisionRankingResponse $response, ?string $fallback): ?string
    {
        try {
            $name = $response->getDivisionName();
        } catch (\Error) {
            // The API did not include the division name in the response.
            return $fallback;
        }

        return '' !== $name ? $name : $fallback;
    }
}
