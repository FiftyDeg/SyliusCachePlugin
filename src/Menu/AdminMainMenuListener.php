<?php

declare(strict_types=1);

namespace FiftyDeg\SyliusCachePlugin\Menu;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final class AdminMainMenuListener
{
    public function addShopSettingsMenu(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();

        $submenu = $menu->getChild('fifty_deg_cache') ?? $menu->addChild('fifty_deg_cache')->setLabel('fifty_deg_sylius_cache.ui.cache');

        $submenu
            ->addChild('shop-settings-fifty-deg-cache-flusher-index', [
                // The route is obtained by replacing app. with app_admin_ in app.app_admin_fifty_deg_cache_flusher route
                'route' => 'app_admin_fifty_deg_cache_index',
            ])
            ->setLabel('fifty_deg_sylius_cache.ui.flusher')
        ;
    }
}
