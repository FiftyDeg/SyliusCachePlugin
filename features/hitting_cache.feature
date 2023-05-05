@hitting_cache
Feature: Hitting cache
    In order to improve performance
    As an application
    I want to fetch template events from cache

    Background:
        Given the cache has been flushed

    @ui
    Scenario: Fetch Sylius template event from cache
        When Jon Doe visits the homepage
        And Foo Bar visits the homepage after Jon Doe
        Then Foo Bar sees Jon Doe cachable content
        Then Foo Bar does not see Jon Doe not cachable content
