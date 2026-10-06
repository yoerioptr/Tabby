<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CompetitionMatch;

/** @extends TabtRepository<CompetitionMatch> */
final class MatchRepository extends TabtRepository
{
    #[\Override]
    protected function modelClass(): string
    {
        return CompetitionMatch::class;
    }

    /**
     * @return list<CompetitionMatch>
     */
    public function findAll(): array
    {
        return $this->hydrate($this->query()->executeQuery()->fetchAllAssociative());
    }

    public function find(string $matchId): ?CompetitionMatch
    {
        $rows = $this->query()
            ->where('matchId = :matchId')
            ->setParameter('matchId', $matchId)
            ->executeQuery()
            ->fetchAllAssociative();

        return $this->hydrate($rows)[0] ?? null;
    }

    /**
     * Scheduled matches ordered by date, excluding matches without a date.
     *
     * @return list<CompetitionMatch>
     */
    public function findByDate(): array
    {
        $rows = $this->query()
            ->where("date IS NOT NULL AND date <> ''")
            ->orderBy('date', 'ASC')
            ->executeQuery()
            ->fetchAllAssociative();

        return $this->hydrate($rows);
    }
}
