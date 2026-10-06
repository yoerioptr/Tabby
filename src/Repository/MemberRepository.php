<?php

declare(strict_types=1);

namespace App\Repository;

use App\Dto\MemberFilter;
use App\Dto\Sort;
use App\Entity\Member;
use App\Service\RankingScale;
use App\Service\Sorter;
use Yoerioptr\TabtApiBundle\ReadModel\ReadModel;

/** @extends TabtRepository<Member> */
final class MemberRepository extends TabtRepository
{
    public function __construct(ReadModel $readModel, private readonly Sorter $sorter)
    {
        parent::__construct($readModel);
    }

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
    public function findByFilter(MemberFilter $filter, Sort $sort = new Sort('ranking')): array
    {
        $query = $this->query();

        if ('' !== ($name = trim((string) $filter->name))) {
            $query
                ->andWhere('(LOWER(firstName) LIKE :name OR LOWER(lastName) LIKE :name OR CAST(id AS TEXT) LIKE :name)')
                ->setParameter('name', '%' . mb_strtolower($name) . '%');
        }

        if ('' !== ($ranking = trim((string) $filter->ranking))) {
            $query
                ->andWhere('ranking = :ranking')
                ->setParameter('ranking', $ranking);
        }

        $members = $this->hydrate($query->executeQuery()->fetchAllAssociative());

        return $this->sorter->sort(
            $members,
            $sort,
            self::comparators(),
            static fn(Member $a, Member $b): int => ($a->getId() ?? 0) <=> ($b->getId() ?? 0),
        );
    }

    /**
     * Natural ascending comparators keyed by sort field. The ranking field
     * follows the ranking strength scale, with the strongest ranking first.
     *
     * @return array<string, callable(Member, Member): int>
     */
    private static function comparators(): array
    {
        return [
            'id' => static fn(Member $a, Member $b): int => ($a->getId() ?? 0) <=> ($b->getId() ?? 0),
            'firstName' => static fn(Member $a, Member $b): int => strcasecmp((string) $a->getFirstName(), (string) $b->getFirstName()),
            'lastName' => static fn(Member $a, Member $b): int => strcasecmp((string) $a->getLastName(), (string) $b->getLastName()),
            'ranking' => static fn(Member $a, Member $b): int => RankingScale::weight((string) $b->getRanking()) <=> RankingScale::weight((string) $a->getRanking()),
        ];
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
