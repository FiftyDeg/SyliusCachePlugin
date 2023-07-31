<?php

declare(strict_types=1);

namespace Tests\FiftyDeg\SyliusCachePlugin\Behat\Page\Shop;

use FriendsOfBehat\PageObjectExtension\Page\SymfonyPage;

class HomePage extends SymfonyPage implements HomePageInterface
{
    public function createElementWithRandomContent(): void
    {
    }

    public function getCacheableElementRandomContent(): string
    {
        return $this->getElement('cacheableElement')->getText();
    }

    public function getNotCacheableElementRandomContent(): string
    {
        return $this->getElement('notCacheableElement')->getText();
    }

    /**
     * @inheritdoc
     */
    public function getRouteName(): string
    {
        return 'sylius_shop_homepage';
    }

    /**
     * @inheritdoc
     */
    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'cacheableElement' => '#cacheableElement',
            'notCacheableElement' => '#notCacheableElement',
        ]);
    }
}
