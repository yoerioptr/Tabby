<?php

declare(strict_types=1);

namespace App\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;

final readonly class MenuBuilder
{
    public function createMainMenu(FactoryInterface $factory): ItemInterface
    {
        $menu = $factory->createItem('root');

        $menu->addChild('Home', ['route' => 'app_tabt_test']);

        return $menu;
    }
}
