<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\RankingController;
use App\Tests\Fixtures\FakeTabtClient;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Yoerioptr\TabtApiClient\Client\ClientInterface;

final class RankingControllerTest extends KernelTestCase
{
    public function testItRendersEveryClubDivisionOnceWithItsRanking(): void
    {
        $container = $this->bootWithRanking();

        $requestStack = $container->get(RequestStack::class);
        $requestStack->push(Request::create('/ranking'));

        $controller = $container->get(RankingController::class);
        $response = $controller->index();

        self::assertSame(200, $response->getStatusCode());

        $html = (string) $response->getContent();

        self::assertStringContainsString('Place', $html);
        self::assertStringContainsString('Team', $html);
        self::assertStringContainsString('Points', $html);

        // Two club teams share the first division, but it must only render once.
        self::assertStringContainsString('Nationale 3A', $html);
        self::assertSame(1, substr_count($html, 'Nationale 3A'));
        self::assertStringContainsString('Nationale 4B', $html);
        self::assertSame(1, substr_count($html, 'Nationale 4B'));

        self::assertStringContainsString('Tabby A', $html);
        self::assertStringContainsString('Rivals A', $html);
        self::assertStringContainsString('Tabby C', $html);

        self::assertStringContainsString('>1</td>', $html);
        self::assertStringContainsString('>12</td>', $html);
        self::assertStringContainsString('>9</td>', $html);
    }

    public function testItShowsAnEmptyStateWhenTheClubHasNoDivisions(): void
    {
        $container = $this->bootWithRanking([
            'GetClubTeams' => [
                'TeamCount' => 0,
                'TeamEntries' => [],
            ],
        ]);

        $requestStack = $container->get(RequestStack::class);
        $requestStack->push(Request::create('/ranking'));

        $controller = $container->get(RankingController::class);
        $response = $controller->index();

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('No divisions found.', (string) $response->getContent());
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function bootWithRanking(array $overrides = []): \Symfony\Component\DependencyInjection\ContainerInterface
    {
        self::bootKernel();

        $container = static::getContainer();
        $container->set(ClientInterface::class, new FakeTabtClient($overrides + [
            'GetClubTeams' => [
                'ClubName' => 'Tabby',
                'TeamCount' => 3,
                'TeamEntries' => [
                    ['TeamId' => '1', 'Team' => 'Tabby A', 'DivisionId' => 12, 'DivisionName' => 'Nationale 3A'],
                    ['TeamId' => '2', 'Team' => 'Tabby B', 'DivisionId' => 12, 'DivisionName' => 'Nationale 3A'],
                    ['TeamId' => '3', 'Team' => 'Tabby C', 'DivisionId' => 15, 'DivisionName' => 'Nationale 4B'],
                ],
            ],
            'GetDivisionRanking' => [
                [
                    'DivisionName' => 'Nationale 3A',
                    'RankingEntries' => [
                        ['Position' => 1, 'Team' => 'Tabby A', 'Points' => 12, 'TeamClub' => 'LK110'],
                        ['Position' => 2, 'Team' => 'Rivals A', 'Points' => 10, 'TeamClub' => 'OP123'],
                        ['Position' => 3, 'Team' => 'Tabby B', 'Points' => 8, 'TeamClub' => 'LK110'],
                    ],
                ],
                [
                    'DivisionName' => 'Nationale 4B',
                    'RankingEntries' => [
                        ['Position' => 1, 'Team' => 'Tabby C', 'Points' => 9, 'TeamClub' => 'LK110'],
                        ['Position' => 2, 'Team' => 'Other Club', 'Points' => 6, 'TeamClub' => 'OP456'],
                    ],
                ],
            ],
        ]));

        return $container;
    }
}
