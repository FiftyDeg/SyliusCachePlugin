<?php

declare(strict_types=1);

namespace Tests\FiftyDeg\SyliusCachePlugin\Behat\Context\Ui\Shop;

use Behat\Behat\Context\Context;
use FiftyDeg\SyliusCachePlugin\Adapters\CacheAdapterInterface;
use Tests\FiftyDeg\SyliusCachePlugin\Behat\Page\Shop\HomePageInterface;
use Webmozart\Assert\Assert;

final class HomePageMissCacheContext implements Context
{
    /**
     * @var HomePageInterface
     */
    private $homePage;

    /**
     * @var string
     */
    private $jonDoeRandomContent;

    /**
     * @var string
     */
    private $fooBarRandomContent;

    /**
     * @var CacheAdapterInterface
     */
    private $cacheAdapter;

    /**
     * @param HomePageInterface $homePage
     */
    public function __construct(
        HomePageInterface $homePage,
        CacheAdapterInterface $cacheAdapter
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

        $this->jonDoeRandomContent = $this->homePage->getCacheableElementRandomContent();
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

        $this->fooBarRandomContent = $this->homePage->getCacheableElementRandomContent();
    }

    /**
     * @Then Foo Bar does not see Jon Doe random content
     */

    public function theHomePageCacheIsNotEmpty(): void
    {
        Assert::notSame(
            $this->jonDoeRandomContent,
            $this->fooBarRandomContent
        );
    }
}
