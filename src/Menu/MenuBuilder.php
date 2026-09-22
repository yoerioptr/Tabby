<?php

declare(strict_types=1);

namespace App\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Bundle\SecurityBundle\Security;

final readonly class MenuBuilder
{
    public function __construct(private Security $security)
    {
    }

    public function createMainMenu(FactoryInterface $factory): ItemInterface
    {
        $menu = $factory->createItem('root');

        $menu->addChild('dashboard', [
            'label' => 'Dashboard',
            'route' => 'app_dashboard',
            'extras' => ['icon' => 'home'],
        ]);
        $menu->addChild('calendar', [
            'label' => 'Calendar',
            'route' => 'app_calendar',
            'extras' => ['icon' => 'calendar'],
        ]);
        $menu->addChild('ranking', [
            'label' => 'Ranking',
            'route' => 'app_ranking',
            'extras' => ['icon' => 'trophy'],
        ]);
        $menu->addChild('members', [
            'label' => 'Members',
            'route' => 'app_members',
            'extras' => ['icon' => 'users'],
        ]);

        return $menu;
    }

    public function createAccountMenu(FactoryInterface $factory): ItemInterface
    {
        $menu = $factory->createItem('root');

        $menu->addChild('profile', [
            'label' => 'Your profile',
            'route' => 'app_profile',
        ]);
        $menu->addChild('logout', [
            'label' => 'Sign out',
            'route' => 'app_logout',
        ]);

        return $menu;
    }

    public function createActionsMenu(FactoryInterface $factory): ItemInterface
    {
        $menu = $factory->createItem('root');

        if (!$this->security->isGranted('ROLE_ADMIN')) {
            return $menu;
        }

        // Contextual action links are added here based on the visited page.

        return $menu;
    }
}
