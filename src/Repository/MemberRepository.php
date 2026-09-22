<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Member;
use Yoerioptr\TabtApiBundle\Doctrine\ApiFetcher;
use Yoerioptr\TabtApiBundle\Doctrine\EntityHydrator;
use Yoerioptr\TabtApiBundle\Doctrine\MappingRegistry;
use Yoerioptr\TabtApiBundle\Repository\AbstractTabtRepository;

/**
 * @extends AbstractTabtRepository<Member>
 */
final class MemberRepository extends AbstractTabtRepository
{
    public function __construct(
        MappingRegistry $mappings,
        EntityHydrator $hydrator,
        ApiFetcher $fetcher,
    ) {
        parent::__construct($mappings, $hydrator, $fetcher);
    }

    protected function getEntityClass(): string
    {
        return Member::class;
    }
}
