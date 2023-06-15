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

    public function isCacheEnabled(): bool
    {
        /** @var bool|null $isCacheEnabled */
        $isCacheEnabled = $this->getParam('is_cache_enabled');

        return (bool) $isCacheEnabled;
    }

    public function getEventCacheTtl(string $eventName): int
    {
        if (!$this->isCacheEnabled()) {
            return 0;
        }

        /** @var array<array-key, array<array-key, mixed>> $templateEvents */
        $templateEvents = $this->getTemplateEvents();

        foreach ($templateEvents as $templateEvent) {
            if ($templateEvent['name'] !== $eventName) {
                continue;
            }

            $blockDefaultTtl = (int) $templateEvent['block_default_ttl'];

            $blocks = isset($templateEvent['blocks'])
                ? (array) $templateEvent['blocks']
                : [];

            if ($blockDefaultTtl > 0 || count($blocks) > 0) {
                // Ignore template event cache settings since there's at least one setting for its blocks
                return 0;
            }

            return (int) $templateEvent['ttl'];
        }

        return 0;
    }

    public function getBlockCacheTtl(string $eventName, string $blockName): int
    {
        $ttl = 0;

        if (!$this->isCacheEnabled()) {
            return $ttl;
        }

        $templateEvents = $this->getTemplateEvents();

        /** @var array $eventCacheConfig */
        foreach ($templateEvents as $eventCacheConfig) {
            if ((string) $eventCacheConfig['name'] !== $eventName) {
                continue;
            }

            if (isset($eventCacheConfig['block_default_ttl'])) {
                $ttl = (int) $eventCacheConfig['block_default_ttl'];
            }

            $templateEventBlocks = $this->getTemplateEventBlocks($eventName);

            /** @var array $templateEventBlock */
            foreach ($templateEventBlocks as $templateEventBlock) {
                if ($templateEventBlock['name'] !== $blockName) {
                    continue;
                }

                return (int) $templateEventBlock['ttl'];
            }
        }

        return $ttl;
    }

    private function getTemplateEvents(): array
    {
        /** @var array|null $templateEvents */
        $templateEvents = $this->getParam('template_events');

        return $templateEvents ?? [];
    }

    private function getTemplateEventBlocks(string $eventName): array
    {
        $templateEvents = $this->getTemplateEvents();

        /** @var array $templateEvent */
        foreach ($templateEvents as $templateEvent) {
            if ((string) $templateEvent['name'] !== $eventName) {
                continue;
            }

            return (array) $templateEvent['blocks'];
        }

        return [];
    }

    private function getParam(string $paramName): mixed
    {
        return $this->parameterBag->has($paramName)
            ? $this->parameterBag->get($paramName)
            : null;
    }
}
