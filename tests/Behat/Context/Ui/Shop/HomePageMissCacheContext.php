<?php

declare(strict_types=1);

namespace Tests\FiftyDeg\SyliusCachePlugin\Behat\Context\Ui\Shop;

use Behat\Behat\Context\Context;
use FiftyDeg\SyliusCachePlugin\Adapters\CacheAdapterInterface;
use Tests\FiftyDeg\SyliusCachePlugin\Behat\Page\Shop\HomePageInterface;
use Webmozart\Assert\Assert;

final class HomePageMissCacheContext implements Context
{
    private $homePage;

    private $jonCachableCont;

    private $jonNotCachableCont;

    private $fooCachableCont;

    private $fooNotCachableCont;

    private $cacheAdapter;

    public function __construct(
        HomePageInterface $homePage,
        CacheAdapterInterface $cacheAdapter,
    ) {
        $this->homePage = $homePage;
        $this->cacheAdapter = $cacheAdapter;
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
     * @When the cache has been flushed
     */
    public function theCacheHasBeenFlushed(): void
    {
        $this->cacheAdapter->flush();
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
     * @Then Foo Bar does not see Jon Doe cachable content
     */
    public function theHomePageCacheIsEmptyOnCachableContent(): void
    {
        Assert::notSame(
            $this->jonCachableCont,
            $this->fooCachableCont,
        );
    }
}
