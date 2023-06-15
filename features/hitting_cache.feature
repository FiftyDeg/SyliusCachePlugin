@hitting_cache
Feature: Hitting cache
    In order to improve performance
    As an application
    I want to fetch template events from cache

    Background:
        Given the cache has been flushed

    @ui
    Scenario: Fetch Sylius template event from cache
        When John Doe visits the homepage
        And Foo Bar visits the homepage after John Doe
        Then Foo Bar sees John Doe cacheable content
