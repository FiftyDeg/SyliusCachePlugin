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
                isset($cacheSettings['ttl']) &&
                $cacheSettings['ttl'] <= 0) {
                return false;
            }
        }

        return true;
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

    public function getEventCacheTTL(string $eventNameToSearchFor): int
    {
        $result = 0;

        if (!$this->isCacheEnabled() ||
            !$this->isEventCacheEnabled($eventNameToSearchFor)) {
            return $result;
        }

        $result = $this->getDefaultEventCacheTtl();

        /** @var array<array-key, array<array-key, mixed>> $cacheableEvents */
        $cacheableEvents = $this->getCacheableSyliusTemplateEvents();

        foreach ($cacheableEvents as $cacheSettings) {
            if ($cacheSettings['name'] === $eventNameToSearchFor) {
                if (isset($cacheSettings['ttl'])) {
                    $result = (int) $cacheSettings['ttl'];
                }

                if ($this->hasInnerBlocksZeroTTL($cacheSettings)) {
                    $result = 0;
                }
            }
        }

        return $result;
    }

    public function getBlockCacheTTL(
        string $eventNameToSearchFor,
        string $blockNameToSearchFor,
    ): int {
        $result = 0;
        if (!$this->isCacheEnabled()) {
            return $result;
        }

        $result = $this->getDefaultEventBlockCacheTtl();

        /** @var array<array-key, array<array-key, array<array-key, mixed>>> $cacheableEvents */
        $cacheableEvents = $this->getCacheableSyliusTemplateEvents();

        foreach ($cacheableEvents as $cacheSettings) {
            /** @var string $blockName */
            $blockName = $cacheSettings['name'];
            if ($blockName === $eventNameToSearchFor) {
                if (isset($cacheSettings['default_event_block_cache_ttl'])) {
                    $result = (int) $cacheSettings['default_event_block_cache_ttl'];
                }

                $innerBlockTTL = $this->getInnerBlocksTTL($cacheSettings, $blockNameToSearchFor);

                if ($innerBlockTTL !== -1) {
                    $result = $innerBlockTTL;
                }
            }
        }

        return $result;
    }

    /**
     * @param array<array-key, mixed> $cacheSettings
     */
    private function hasInnerBlocksZeroTTL($cacheSettings): bool
    {
        if (isset($cacheSettings['blocks']) &&
            is_array($cacheSettings['blocks']) &&
            count($cacheSettings['blocks']) > 0) {
            /** @var array<array-key, array<array-key, mixed>> $cacheBlocks */
            $cacheBlocks = $cacheSettings['blocks'];

            foreach ($cacheBlocks as $eventBlock) {
                if (isset($eventBlock['ttl']) &&
                    $eventBlock['ttl'] === 0) {
                    return true;
                }
            }

            return false;
        }

        return false;
    }

    /**
     * @param array<array-key, array<array-key, mixed>> $cacheSettings
     * @param string $blockNameToSearchFor
     */
    private function getInnerBlocksTTL($cacheSettings, $blockNameToSearchFor): int
    {
        if (isset($cacheSettings['blocks']) &&
            count($cacheSettings['blocks']) > 0) {
            /** @var array<array-key, array<array-key, mixed>> $cacheBlocks */
            $cacheBlocks = $cacheSettings['blocks'];

            return $this->getInnerBlockTTL($cacheBlocks, $blockNameToSearchFor);
        }

        return -1;
    }

    /**
     * @param array<array-key, array<array-key, mixed>> $cacheBlocks
     * @param string $blockNameToSearchFor
     */
    private function getInnerBlockTTL($cacheBlocks, $blockNameToSearchFor): int
    {
        foreach ($cacheBlocks as $eventBlock) {
            if ($eventBlock['name'] === $blockNameToSearchFor) {
                if (isset($eventBlock['ttl'])) {
                    /** @var int $eventTtl */
                    $eventTtl = $eventBlock['ttl'];

                    return $eventTtl;
                }
            }
        }

        return -1;
    }

    private function getParam(string $paramName): mixed
    {
        return $this->parameterBag->has($paramName)
            ? $this->parameterBag->get($paramName)
            : null;
    }
}
