<?php

declare(strict_types=1);

namespace Tests\FiftyDeg\SyliusCachePlugin\Behat\Page\Shop;

use FriendsOfBehat\PageObjectExtension\Page\SymfonyPageInterface;

interface HomePageInterface extends SymfonyPageInterface
{
    /**
     * @return string
     */
    public function getCacheableElementRandomContent(): string;
}
