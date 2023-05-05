<?php

declare(strict_types=1);

namespace FiftyDeg\SyliusCachePlugin\ConfigLoader;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

final class ConfigLoader implements ConfigLoaderInterface
{
    public function __construct(
        private ParameterBag $parameterBag,
    ) {
    }

    public function getCacheableSyliusTemplateEvents(): array
    {
        /** @var array|null $cacheableEvents */
        $cacheableEvents = $this->getParam('cacheable_sylius_template_events');

        return $cacheableEvents ?? [];
    }

    public function isCacheEnabled(): bool
    {
        /** @var bool|null $isCacheEnabled */
        $isCacheEnabled = $this->getParam('is_cache_enabled');
        if (null === $isCacheEnabled) {
            $isCacheEnabled = false;
        }

        return $isCacheEnabled;
    }

    private function isEventCacheEnabled(string $eventNameToSearchFor): bool
    {
        /** @var array<array-key, array<array-key, mixed>> $cacheableEvents */
        $cacheableEvents = $this->getCacheableSyliusTemplateEvents();

        foreach ($cacheableEvents as $cacheSettings) {
            if ($cacheSettings['name'] === $eventNameToSearchFor &&
                isset($cacheSettings['is_cache_enabled']) &&
                !$cacheSettings['is_cache_enabled']) {
                return false;
            }
        }

        return true;
    }

    private function getDefaultEventCacheEnabled(): bool
    {
        /** @var bool $defCacheEnabled */
        $defCacheEnabled = $this->getParam('default_event_cache_enabled');

        return $defCacheEnabled;
    }

    private function getDefaultEventBlockCacheEnabled(): bool
    {
        /** @var bool $defCacheEnabled */
        $defCacheEnabled = $this->getParam('default_event_block_cache_enabled');

        return $defCacheEnabled;
    }

    private function getDefaultEventCacheTtl(): int
    {
        /** @var int $defaultTtl */
        $defaultTtl = $this->getParam('default_event_cache_ttl');

        return $defaultTtl;
    }

    private function getDefaultEventBlockCacheTtl(): int
    {
        /** @var int $defaultTtl */
        $defaultTtl = $this->getParam('default_event_block_cache_ttl');

        return $defaultTtl;
    }

    public function checkEventCache(string $eventNameToSearchFor): array
    {
        $result = [
            'cacheEnabled' => false,
            'ttl' => 0,
        ];

        if (!$this->isCacheEnabled() ||
            !$this->isEventCacheEnabled($eventNameToSearchFor)) {
            return $result;
        }

        $result['cacheEnabled'] = $this->getDefaultEventCacheEnabled() && $this->isCacheEnabled();
        $result['ttl'] = $this->getDefaultEventCacheTtl();

        /** @var array<array-key, array<array-key, mixed>> $cacheableEvents */
        $cacheableEvents = $this->getCacheableSyliusTemplateEvents();

        foreach ($cacheableEvents as $cacheSettings) {
            if ($cacheSettings['name'] === $eventNameToSearchFor) {
                /** @var bool $defaultBlockCacheEn */
                $defaultBlockCacheEn = $cacheSettings['default_event_block_cache_enabled'];

                if (isset($cacheSettings['is_cache_enabled'])) {
                    /** @var bool $isEnabled */
                    $isEnabled = $cacheSettings['is_cache_enabled'];

                    $result['cacheEnabled'] = $isEnabled;
                }
                if (isset($cacheSettings['ttl'])) {
                    $result['ttl'] = (int) $cacheSettings['ttl'];
                }

                if (isset($cacheSettings['blocks']) &&
                    is_array($cacheSettings['blocks']) &&
                    count($cacheSettings['blocks']) > 0) {
                    /** @var array<array-key, array<array-key, mixed>> $cacheBlocks */
                    $cacheBlocks = $cacheSettings['blocks'];

                    foreach ($cacheBlocks as $eventBlock) {
                        /** @var bool $blockCacheEnabled */
                        $blockCacheEnabled = $eventBlock['is_cache_enabled'];
                        if ((isset($eventBlock['is_cache_enabled']) && !$blockCacheEnabled) ||
                            (!isset($eventBlock['is_cache_enabled']) && !$defaultBlockCacheEn)) {
                            $result['cacheEnabled'] = false;
                            $result['ttl'] = 0;
                        }
                    }
                }
            }
        }

        return $result;
    }

    public function checkEventBlockCache(
        string $eventNameToSearchFor,
        string $blockNameToSearchFor,
    ): array {
        $result = [
            'cacheEnabled' => false,
            'ttl' => 0,
        ];

        if (!$this->isCacheEnabled() ||
            !$this->isEventCacheEnabled($eventNameToSearchFor)) {
            return $result;
        }

        $result['cacheEnabled'] = $this->getDefaultEventBlockCacheEnabled() && $this->isCacheEnabled();
        $result['ttl'] = $this->getDefaultEventBlockCacheTtl();

        /** @var array<array-key, array<array-key, mixed>> $cacheableEvents */
        $cacheableEvents = $this->getCacheableSyliusTemplateEvents();

        foreach ($cacheableEvents as $cacheSettings) {
            if ($cacheSettings['name'] === $eventNameToSearchFor) {
                if (isset($cacheSettings['default_event_block_cache_enabled'])) {
                    /** @var bool $isEnabled */
                    $isEnabled = $cacheSettings['default_event_block_cache_enabled'];

                    $result['cacheEnabled'] = $isEnabled;
                }
                if (isset($cacheSettings['default_event_block_cache_ttl'])) {
                    $result['ttl'] = (int) $cacheSettings['default_event_block_cache_ttl'];
                }

                if (isset($cacheSettings['blocks']) &&
                    is_array($cacheSettings['blocks']) &&
                    count($cacheSettings['blocks']) > 0) {
                    /** @var array<array-key, array<array-key, mixed>> $cacheBlocks */
                    $cacheBlocks = $cacheSettings['blocks'];

                    $result = $this->checkInnerBlock($result, $cacheBlocks, $blockNameToSearchFor);
                }
            }
        }

        $result['shouldUseCache'] = false;
        if ($result['cacheEnabled'] && $result['ttl'] > 0) {
            $result['shouldUseCache'] = true;
        }

        return $result;
    }

    /** 
     * @param array<array-key, mixed> $result
     * @param array<array-key, array<array-key, mixed>> $cacheBlocks
     * @param string $blockNameToSearchFor 
     */
    private function checkInnerBlock($result, $cacheBlocks, $blockNameToSearchFor): array
    {
        foreach ($cacheBlocks as $eventBlock) {
            if ($eventBlock['name'] === $blockNameToSearchFor) {
                if (isset($eventBlock['is_cache_enabled'])) {
                    /** @var bool $isCached */
                    $isCached = $eventBlock['is_cache_enabled'];

                    $result['cacheEnabled'] = $isCached;
                }
                if (isset($eventBlock['ttl'])) {
                    /** @var int $eventTtl */
                    $eventTtl = $eventBlock['ttl'];

                    $result['ttl'] = $eventTtl;
                }
            }
        }

        return $result;
    }

    private function getParam(string $paramName): mixed
    {
        return $this->parameterBag->has($paramName)
            ? $this->parameterBag->get($paramName)
            : null;
    }
}
