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

    public function getCacheableTemplateEvents(): array
    {
        /** @var array|null $cacheableEvents */
        $cacheableEvents = $this->getParam('cacheable_sylius_template_events');

        return $cacheableEvents ?? [];
    }

    public function isCacheEnabled(): bool
    {
        /** @var bool|null $isCacheEnabled */
        $isCacheEnabled = $this->getParam('is_cache_enabled');

        return (bool) $isCacheEnabled;
    }

    public function getEventCacheTtl(string $eventName): int
    {
        if (
            !$this->isCacheEnabled() ||
            !$this->isEventCacheEnabled($eventName)
        ) {
            return 0;
        }

        /** @var array<array-key, array<array-key, mixed>> $cacheableEvents */
        $cacheableEvents = $this->getCacheableTemplateEvents();

        foreach ($cacheableEvents as $cacheSettings) {
            if ($cacheSettings['name'] !== $eventName) {
                continue;
            }

            if (!$this->isEventCacheable($cacheSettings)) {
                return 0;
            }

            if (isset($cacheSettings['ttl'])) {
                return (int) $cacheSettings['ttl'];
            }
        }

        return $this->getEventDefaultCacheTtl();
    }

    public function getBlockCacheTTL(string $eventName, string $blockName): int
    {
        $result = 0;

        if (!$this->isCacheEnabled()) {
            return $result;
        }

        $result = $this->getBlockDefaultCacheTtl();

        /** @var array<array-key, array<array-key, array<array-key, mixed>>> $cacheableEvents */
        $cacheableEvents = $this->getCacheableTemplateEvents();

        foreach ($cacheableEvents as $cacheSettings) {
            /** @var string $blockName */
            $blockName = $cacheSettings['name'];

            if ($blockName === $eventName) {
                if (isset($cacheSettings['default_block_cache_ttl'])) {
                    $result = (int) $cacheSettings['default_block_cache_ttl'];
                }

                $innerBlockTTL = $this->getInnerBlocksTTL($cacheSettings, $blockName);

                if ($innerBlockTTL !== -1) {
                    $result = $innerBlockTTL;
                }
            }
        }

        return $result;
    }

    private function isEventCacheEnabled(string $eventName): bool
    {
        /** @var array<array-key, array<array-key, mixed>> $cacheableEvents */
        $cacheableEvents = $this->getCacheableTemplateEvents();

        foreach ($cacheableEvents as $cacheSettings) {
            if (
                $cacheSettings['name'] === $eventName &&
                isset($cacheSettings['ttl']) &&
                $cacheSettings['ttl'] <= 0
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<array-key, mixed> $cacheSettings
     */
    private function isEventCacheable($cacheSettings): bool
    {
        if (isset($cacheSettings['blocks']) &&
            is_array($cacheSettings['blocks']) &&
            count($cacheSettings['blocks']) > 0) {
            /** @var array<array-key, array<array-key, mixed>> $cacheBlocks */
            $cacheBlocks = $cacheSettings['blocks'];

            foreach ($cacheBlocks as $eventBlock) {
                if (isset($eventBlock['ttl']) &&
                    $eventBlock['ttl'] > 0) {
                    return true;
                }
            }

            return false;
        }

        return false;
    }

    private function getEventDefaultCacheTtl(): int
    {
        /** @var int $defaultTtl */
        $defaultTtl = $this->getParam('default_event_cache_ttl');

        return $defaultTtl;
    }

    private function getBlockDefaultCacheTtl(): int
    {
        /** @var int $defaultTtl */
        $defaultTtl = $this->getParam('default_block_cache_ttl');

        return $defaultTtl;
    }

    /**
     * @param array<array-key, array<array-key, mixed>> $cacheSettings
     * @param string $blockName
     */
    private function getInnerBlocksTTL($cacheSettings, $blockName): int
    {
        if (isset($cacheSettings['blocks']) &&
            count($cacheSettings['blocks']) > 0) {
            /** @var array<array-key, array<array-key, mixed>> $cacheBlocks */
            $cacheBlocks = $cacheSettings['blocks'];

            return $this->getInnerBlockTTL($cacheBlocks, $blockName);
        }

        return -1;
    }

    /**
     * @param array<array-key, array<array-key, mixed>> $cacheBlocks
     * @param string $blockName
     */
    private function getInnerBlockTTL($cacheBlocks, $blockName): int
    {
        foreach ($cacheBlocks as $eventBlock) {
            if ($eventBlock['name'] === $blockName) {
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
