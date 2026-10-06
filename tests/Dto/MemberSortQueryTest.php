<?php

declare(strict_types=1);

namespace App\Tests\Dto;

use App\Dto\MemberSortQuery;
use App\Enum\SortDirection;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class MemberSortQueryTest extends KernelTestCase
{
    public function testItAcceptsEveryAllowedColumnAndDirection(): void
    {
        foreach (MemberSortQuery::SORTABLE_FIELDS as $field) {
            $query = new MemberSortQuery();
            $query->sort = $field;
            $query->direction = 'desc';

            self::assertCount(0, $this->validator()->validate($query));
        }
    }

    public function testItRejectsAnUnknownSortColumn(): void
    {
        $query = new MemberSortQuery();
        $query->sort = 'unknown';

        self::assertGreaterThan(0, $this->validator()->validate($query)->count());
    }

    public function testItRejectsAnUnknownDirection(): void
    {
        $query = new MemberSortQuery();
        $query->direction = 'sideways';

        self::assertGreaterThan(0, $this->validator()->validate($query)->count());
    }

    public function testItDefaultsToRankingAscending(): void
    {
        $sort = (new MemberSortQuery())->toSort();

        self::assertSame('ranking', $sort->field);
        self::assertSame(SortDirection::Asc, $sort->direction);
    }

    public function testItMapsTheQueryToAGenericSort(): void
    {
        $query = new MemberSortQuery();
        $query->sort = 'lastName';
        $query->direction = 'desc';

        $sort = $query->toSort();

        self::assertSame('lastName', $sort->field);
        self::assertSame(SortDirection::Desc, $sort->direction);
    }

    private function validator(): ValidatorInterface
    {
        self::bootKernel();

        return self::getContainer()->get('validator');
    }
}
