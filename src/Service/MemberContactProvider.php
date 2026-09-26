<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Member;

/**
 * Resolves the users linked to a member. Linking users to members is not
 * implemented yet, so fictitious contacts are returned for now. Once the
 * User <-> Member relation exists this provider should be replaced.
 */
final class MemberContactProvider
{
    /**
     * @var list<array{name: string, phone: string, email: string, address: string}>
     */
    private const array CONTACTS = [
        [
            'name' => 'Marie Peeters',
            'phone' => '+32 470 12 34 56',
            'email' => 'marie.peeters@example.com',
            'address' => 'Kerkstraat 12, 9100 Sint-Niklaas',
        ],
        [
            'name' => 'Thomas Vermeulen',
            'phone' => '+32 471 98 76 54',
            'email' => 'thomas.vermeulen@example.com',
            'address' => 'Stationslaan 45, 9100 Sint-Niklaas',
        ],
    ];

    /**
     * @return list<array{name: string, phone: string, email: string, address: string}>
     */
    public function forMember(Member $member): array
    {
        return self::CONTACTS;
    }
}
