<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\Members\ListMembers;
use App\Controller\Members\ViewMember;
use App\Dto\MemberSortQuery;
use App\Tests\Fixtures\FakeTabtClient;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Yoerioptr\TabtApiClient\Client\ClientInterface;

final class MembersControllerTest extends KernelTestCase
{
    private const int MEMBER_ID = 508072;

    private const int TEAMMATE_ID = 516661;

    private const int OPPONENT_STRONG_ID = 510684;

    private const int OPPONENT_WEAK_ID = 506962;

    public function testItRendersTheMemberWithContactInfoAndResults(): void
    {
        $container = $this->bootWithMembers();

        $requestStack = $container->get(RequestStack::class);
        $requestStack->push(Request::create('/members/'.self::MEMBER_ID));

        $controller = $container->get(ViewMember::class);
        $response = $controller(self::MEMBER_ID);

        self::assertSame(200, $response->getStatusCode());

        $html = (string) $response->getContent();

        self::assertStringContainsString('PAUL BAENS', $html);

        self::assertStringContainsString('Contact information', $html);
        self::assertStringContainsString('Marie Peeters', $html);
        self::assertStringContainsString('marie.peeters@example.com', $html);
        self::assertStringContainsString('+32 470 12 34 56', $html);
        self::assertStringContainsString('tel:+32470123456', $html);
        self::assertStringContainsString('Kerkstraat 12, 9100 Sint-Niklaas', $html);

        self::assertStringContainsString('Current ranking', $html);
        self::assertStringContainsString('Current elo', $html);
        self::assertStringContainsString('Estimated ranking', $html);
        self::assertStringContainsString('Matches won', $html);
        self::assertStringContainsString('Matches lost', $html);

        // Ranking badge in the header, plus the two opponent rankings faced.
        self::assertStringContainsString('>D2<', $html);
        self::assertStringContainsString('>D0</td>', $html);
        self::assertStringContainsString('>D6</td>', $html);

        // Two wins (one singles, one doubles) against D0 and one loss against D6.
        self::assertMatchesRegularExpression('/>D0<\/td>\s*<td[^>]*>2<\/td>\s*<td[^>]*>0<\/td>/', $html);
        self::assertMatchesRegularExpression('/>D6<\/td>\s*<td[^>]*>0<\/td>\s*<td[^>]*>1<\/td>/', $html);

        self::assertStringContainsString('N/A', $html);
    }

    public function testItReturnsNotFoundForAnUnknownMember(): void
    {
        $container = $this->bootWithMembers();

        $controller = $container->get(ViewMember::class);

        $this->expectException(NotFoundHttpException::class);

        $controller(999999);
    }

    public function testItFiltersMembersByRanking(): void
    {
        $container = $this->bootWithMembers();

        $response = $this->index($container, ['member_filter' => ['ranking' => 'D4']]);

        self::assertSame(200, $response->getStatusCode());

        $html = (string) $response->getContent();

        self::assertStringContainsString('TOM', $html);
        self::assertStringNotContainsString('PAUL', $html);
    }

    public function testItFiltersMembersByNameCaseInsensitively(): void
    {
        $container = $this->bootWithMembers();

        $response = $this->index($container, ['member_filter' => ['name' => 'bAeNs']]);

        self::assertSame(200, $response->getStatusCode());

        $html = (string) $response->getContent();

        self::assertStringContainsString('PAUL', $html);
        self::assertStringNotContainsString('TOM', $html);
    }

    public function testItRendersRankingOptionsFromBestToWorst(): void
    {
        $container = $this->bootWithMembers();

        $response = $this->index($container, []);

        $html = (string) $response->getContent();

        self::assertMatchesRegularExpression(
            '/value="D2"[^>]*>D2<\/option>.*value="D4"[^>]*>D4<\/option>/s',
            $html,
        );
    }

    public function testItFiltersMembersByFullIdThroughTheNameFilter(): void
    {
        $container = $this->bootWithMembers();

        $response = $this->index($container, ['member_filter' => ['name' => (string) self::MEMBER_ID]]);

        self::assertSame(200, $response->getStatusCode());

        $html = (string) $response->getContent();

        self::assertStringContainsString('PAUL', $html);
        self::assertStringNotContainsString('TOM', $html);
    }

    public function testItFiltersMembersByPartialIdThroughTheNameFilter(): void
    {
        $container = $this->bootWithMembers();

        $response = $this->index($container, ['member_filter' => ['name' => '516']]);

        self::assertSame(200, $response->getStatusCode());

        $html = (string) $response->getContent();

        self::assertStringContainsString('TOM', $html);
        self::assertStringNotContainsString('PAUL', $html);
    }

    public function testItSortsByRankingStrongestFirstByDefault(): void
    {
        $container = $this->bootWithMembers();

        $response = $this->index($container, []);

        $html = (string) $response->getContent();

        // D2 is stronger than D4, so PAUL comes before TOM by default.
        self::assertLessThan(
            strpos($html, 'TOM'),
            strpos($html, 'PAUL'),
        );
    }

    public function testItSortsByIdAscending(): void
    {
        $container = $this->bootWithMembers();

        $response = $this->index($container, ['sort' => 'id', 'direction' => 'asc']);

        $html = (string) $response->getContent();

        self::assertLessThan(
            strpos($html, 'TOM'),
            strpos($html, 'PAUL'),
        );
    }

    public function testItSortsByLastNameDescending(): void
    {
        $container = $this->bootWithMembers();

        $response = $this->index($container, ['sort' => 'lastName', 'direction' => 'desc']);

        $html = (string) $response->getContent();

        // DE RON sorts before BAENS when descending.
        self::assertLessThan(
            strpos($html, 'PAUL'),
            strpos($html, 'TOM'),
        );
    }

    public function testItRendersSortLinksWithDirectionParameters(): void
    {
        $container = $this->bootWithMembers();

        $response = $this->index($container, []);

        $html = (string) $response->getContent();

        self::assertStringContainsString('sort=id', $html);
        self::assertStringContainsString('direction=asc', $html);
        self::assertStringContainsString('sort=ranking', $html);
        self::assertStringContainsString('direction=desc', $html);
    }

    /**
     * @param array<string, mixed> $query
     */
    private function index(
        \Symfony\Component\DependencyInjection\ContainerInterface $container,
        array $query,
    ): \Symfony\Component\HttpFoundation\Response {
        $request = Request::create('/members', 'GET', $query);
        $container->get(RequestStack::class)->push($request);

        $sortQuery = new MemberSortQuery();
        $sortQuery->sort = \is_string($query['sort'] ?? null) ? $query['sort'] : null;
        $sortQuery->direction = \is_string($query['direction'] ?? null) ? $query['direction'] : null;

        return $container->get(ListMembers::class)($request, $sortQuery);
    }

    private function bootWithMembers(): \Symfony\Component\DependencyInjection\ContainerInterface
    {
        self::bootKernel();

        $container = static::getContainer();
        $container->set(ClientInterface::class, new FakeTabtClient([
            'GetMembers' => [
                'MemberCount' => 2,
                'MemberEntries' => [
                    ['Position' => 1, 'UniqueIndex' => self::MEMBER_ID, 'RankingIndex' => 1, 'FirstName' => 'PAUL', 'LastName' => 'BAENS', 'Ranking' => 'D2'],
                    ['Position' => 2, 'UniqueIndex' => self::TEAMMATE_ID, 'RankingIndex' => 2, 'FirstName' => 'TOM', 'LastName' => 'DE RON', 'Ranking' => 'D4'],
                ],
            ],
            'GetMatches' => [
                'MatchCount' => 2,
                'TeamMatchesEntries' => [
                    [
                        'MatchId' => 'PL/KH02/054',
                        'MatchUniqueId' => 12345,
                        'WeekName' => '01',
                        'Date' => '2020-01-10',
                        'Time' => '20:00:00',
                        'HomeClub' => 'LK058',
                        'HomeTeam' => 'Tabby A',
                        'AwayClub' => 'OP123',
                        'AwayTeam' => 'Rivals A',
                        'DivisionId' => 12,
                        'MatchDetails' => [
                            'DetailsCreated' => true,
                            'HomePlayers' => [
                                'PlayerCount' => 2,
                                'Players' => [
                                    ['Position' => 1, 'UniqueIndex' => self::MEMBER_ID, 'FirstName' => 'PAUL', 'LastName' => 'BAENS', 'Ranking' => 'D2'],
                                    ['Position' => 2, 'UniqueIndex' => self::TEAMMATE_ID, 'FirstName' => 'TOM', 'LastName' => 'DE RON', 'Ranking' => 'D4'],
                                ],
                            ],
                            'AwayPlayers' => [
                                'PlayerCount' => 2,
                                'Players' => [
                                    ['Position' => 1, 'UniqueIndex' => self::OPPONENT_STRONG_ID, 'FirstName' => 'DIRK', 'LastName' => 'BIJNENS', 'Ranking' => 'D0'],
                                    ['Position' => 2, 'UniqueIndex' => self::OPPONENT_WEAK_ID, 'FirstName' => 'RAYMOND', 'LastName' => 'DEKENS', 'Ranking' => 'D6'],
                                ],
                            ],
                            'IndividualMatchResults' => [
                                [
                                    'Position' => 1,
                                    'HomePlayerMatchIndex' => 1,
                                    'HomePlayerUniqueIndex' => self::MEMBER_ID,
                                    'AwayPlayerMatchIndex' => 1,
                                    'AwayPlayerUniqueIndex' => self::OPPONENT_STRONG_ID,
                                    'HomeSetCount' => 3,
                                    'AwaySetCount' => 1,
                                ],
                                [
                                    'Position' => 2,
                                    'HomePlayerMatchIndex' => [1, 2],
                                    'HomePlayerUniqueIndex' => [self::MEMBER_ID, self::TEAMMATE_ID],
                                    'AwayPlayerMatchIndex' => [1, 2],
                                    'AwayPlayerUniqueIndex' => [self::OPPONENT_STRONG_ID, self::OPPONENT_WEAK_ID],
                                    'HomeSetCount' => 3,
                                    'AwaySetCount' => 2,
                                ],
                            ],
                            'MatchSystem' => 2,
                        ],
                    ],
                    [
                        'MatchId' => 'PL/KH02/055',
                        'MatchUniqueId' => 12346,
                        'WeekName' => '02',
                        'Date' => '2020-01-17',
                        'Time' => '20:00:00',
                        'HomeClub' => 'OP456',
                        'HomeTeam' => 'Other Rivals',
                        'AwayClub' => 'LK058',
                        'AwayTeam' => 'Tabby A',
                        'DivisionId' => 12,
                        'MatchDetails' => [
                            'DetailsCreated' => true,
                            'HomePlayers' => [
                                'PlayerCount' => 1,
                                'Players' => [
                                    ['Position' => 1, 'UniqueIndex' => self::OPPONENT_WEAK_ID, 'FirstName' => 'RAYMOND', 'LastName' => 'DEKENS', 'Ranking' => 'D6'],
                                ],
                            ],
                            'AwayPlayers' => [
                                'PlayerCount' => 1,
                                'Players' => [
                                    ['Position' => 1, 'UniqueIndex' => self::MEMBER_ID, 'FirstName' => 'PAUL', 'LastName' => 'BAENS', 'Ranking' => 'D2'],
                                ],
                            ],
                            'IndividualMatchResults' => [
                                [
                                    'Position' => 1,
                                    'HomePlayerMatchIndex' => 1,
                                    'HomePlayerUniqueIndex' => self::OPPONENT_WEAK_ID,
                                    'AwayPlayerMatchIndex' => 1,
                                    'AwayPlayerUniqueIndex' => self::MEMBER_ID,
                                    'HomeSetCount' => 3,
                                    'AwaySetCount' => 1,
                                ],
                            ],
                            'MatchSystem' => 2,
                        ],
                    ],
                ],
            ],
        ]));

        return $container;
    }
}
