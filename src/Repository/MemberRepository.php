<?php

declare(strict_types=1);

namespace App\Repository;

use App\Dto\MemberFilter;
use App\Entity\Member;
use App\Service\RankingScale;

/** @extends TabtRepository<Member> */
final class MemberRepository extends TabtRepository
{
    #[\Override]
    protected function modelClass(): string
    {
        return Member::class;
    }

    public function find(int $id): ?Member
    {
        $rows = $this->query()
            ->where('id = :id')
            ->setParameter('id', $id)
            ->executeQuery()
            ->fetchAllAssociative();

        return $this->hydrate($rows)[0] ?? null;
    }

    /**
     * @return list<Member>
     */
    public function findByFilter(MemberFilter $filter): array
    {
        $query = $this->query();

        if ('' !== ($name = trim((string) $filter->name))) {
            $query
                ->andWhere('(LOWER(firstName) LIKE :name OR LOWER(lastName) LIKE :name)')
                ->setParameter('name', '%' . mb_strtolower($name) . '%');
        }

        if ('' !== ($ranking = trim((string) $filter->ranking))) {
            $query
                ->andWhere('ranking = :ranking')
                ->setParameter('ranking', $ranking);
        }

        return $this->hydrate($query->executeQuery()->fetchAllAssociative());
    }

    /**
     * Distinct rankings held by the members, ordered from strongest to weakest.
     *
     * @return list<string>
     */
    public function distinctRankings(): array
    {
        $rankings = $this->query()
            ->select('DISTINCT ranking')
            ->where("ranking IS NOT NULL AND ranking <> ''")
            ->executeQuery()
            ->fetchFirstColumn();

        return RankingScale::sort($rankings);
    }
}
