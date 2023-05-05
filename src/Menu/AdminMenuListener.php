<?php

declare(strict_types=1);

namespace FiftyDeg\SyliusCachePlugin\Menu;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final class AdminMenuListener
{
    public function addShopSettingsMenu(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();

        $newSubmenu =
            $menu->getChild('fiftydeg_cache')
            ?: $menu->addChild('fiftydeg_cache')->setLabel('Cache');

        $newSubmenu
            ->addChild('shop-settings-fiftydeg-cache-flusher-index', [
                // The route is obtained by replacing app. with app_admin_ in app.app_admin_fifty_deg_cache_flusher route
                'route' => 'app_admin_fifty_deg_cache_index',
            ])
            ->setLabel('Flusher')
        ;
    }
}
