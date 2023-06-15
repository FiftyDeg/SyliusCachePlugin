<?php

declare(strict_types=1);

namespace Tests\FiftyDeg\SyliusCachePlugin\Behat\Context\Ui\Shop;

use Behat\Behat\Context\Context;
use FiftyDeg\SyliusCachePlugin\Adapters\CacheAdapterInterface;
use Tests\FiftyDeg\SyliusCachePlugin\Behat\Page\Shop\HomePageInterface;
use Webmozart\Assert\Assert;

final class HomePageHitCacheContext implements Context
{
    private $johnCacheableCont;

    private $johnNotCacheableCont;

    private $fooCacheableCont;

    private $fooNotCacheableCont;

    public function __construct(
        private HomePageInterface $homePage,
        private CacheAdapterInterface $cacheAdapter,
    ) {
        $this->homePage = $homePage;
        $this->cacheAdapter = $cacheAdapter;
    }

    /**
     * @Given the cache has been flushed
     */
    public function theCacheHasBeenFlushed(): void
    {
        $this->cacheAdapter->flush();
    }

    /**
     * @When John Doe visits the homepage
     */
    public function johnDoeVisitsHomePage(): void
    {
        $this->homePage->open();

        $this->johnCacheableCont = $this->homePage->getCacheableElementRandomContent();

        $this->johnNotCacheableCont = $this->homePage->getNotCacheableElementRandomContent();
    }

    /**
     * @When Foo Bar visits the homepage after John Doe
     */
    public function fooBarVisitsHomePageAfterJohnDoe(): void
    {
        $this->homePage->open();

        $this->fooCacheableCont = $this->homePage->getCacheableElementRandomContent();

        $this->fooNotCacheableCont = $this->homePage->getNotCacheableElementRandomContent();
    }

    /**
     * @Then Foo Bar sees John Doe cacheable content
     */
    public function theHomePageCacheIsNotEmptyOnCacheableContent(): void
    {
        Assert::same(
            $this->johnCacheableCont,
            $this->fooCacheableCont,
        );
    }
}
