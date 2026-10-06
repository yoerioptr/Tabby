<?php

declare(strict_types=1);

namespace App\Dto;

use App\Enum\SortDirection;
use Symfony\Component\Validator\Constraints as Assert;

final class MemberSortQuery
{
    /**
     * @var list<string>
     */
    public const array SORTABLE_FIELDS = ['ranking', 'id', 'firstName', 'lastName'];

    #[Assert\Choice(
        choices: self::SORTABLE_FIELDS,
        message: 'Unknown sort column "{{ value }}".',
    )]
    public ?string $sort = null;

    #[Assert\Choice(
        choices: ['asc', 'desc'],
        message: 'The sort direction must be either "asc" or "desc".',
    )]
    public ?string $direction = null;

    public function toSort(): Sort
    {
        $field = $this->sort ?? self::SORTABLE_FIELDS[0];

        return new Sort(
            \in_array($field, self::SORTABLE_FIELDS, true) ? $field : self::SORTABLE_FIELDS[0],
            SortDirection::tryFrom($this->direction ?? '') ?? SortDirection::Asc,
        );
    }
}
