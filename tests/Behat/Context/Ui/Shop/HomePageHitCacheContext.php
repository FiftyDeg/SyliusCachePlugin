<?php

declare(strict_types=1);

namespace Tests\FiftyDeg\SyliusCachePlugin\Behat\Context\Ui\Shop;

use Behat\Behat\Context\Context;
use FiftyDeg\SyliusCachePlugin\Adapters\CacheAdapterInterface;
use Tests\FiftyDeg\SyliusCachePlugin\Behat\Page\Shop\HomePageInterface;
use Webmozart\Assert\Assert;

final class HomePageHitCacheContext implements Context
{
    private $jonCachableCont;

    private $jonNotCachableCont;

    private $fooCachableCont;

    private $fooNotCachableCont;

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
     * @When Jon Doe visits the homepage
     */
    public function jonDoeVisitsHomePage(): void
    {
        $this->homePage->open();

        $this->jonCachableCont = $this->homePage->getCacheableElementRandomContent();

        $this->jonNotCachableCont = $this->homePage->getNotCacheableElementRandomContent();
    }

    /**
     * @When Foo Bar visits the homepage after Jon Doe
     */
    public function fooBarVisitsHomePageAfterJonDoe(): void
    {
        $this->homePage->open();

        $this->fooCachableCont = $this->homePage->getCacheableElementRandomContent();

        $this->fooNotCachableCont = $this->homePage->getNotCacheableElementRandomContent();
    }

    /**
     * @Then Foo Bar sees Jon Doe cachable content
     */
    public function theHomePageCacheIsNotEmptyOnCachableContent(): void
    {
        Assert::same(
            $this->jonCachableCont,
            $this->fooCachableCont,
        );
    }
}
