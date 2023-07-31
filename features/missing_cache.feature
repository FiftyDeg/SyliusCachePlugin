@missing_cache
Feature: Missing cache
    In order to show new content
    As an administrator
    I need to flush cache

    @ui
    Scenario: Fetch Sylius template event from cache
        When John Doe visits the homepage
        And the cache has been flushed
        And Foo Bar visits the homepage after John Doe
        Then Foo Bar does not see John Doe cacheable content
