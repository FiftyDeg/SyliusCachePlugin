<?php

declare(strict_types=1);

namespace FiftyDeg\SyliusCachePlugin\ConfigLoader;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

final class ConfigLoader implements ConfigLoaderInterface
{
    public function __construct(
        private ParameterBag $parameterBag
    )
    {
    }

    public function getCacheableSyliusTemplateEvents(): array
    {
        return $this->getParam('cacheable_sylius_template_events') ?? [];
    }

    public function isCacheEnabled(): bool
    {
        return $this->getParam('is_cache_enabled');
    }

    private function isEventCacheEnabled($eventNameToSearchFor): bool
    {
        $cacheableTemplateEvents = $this->getCacheableSyliusTemplateEvents();

        foreach($cacheableTemplateEvents as $cacheSettings) {
            if ($cacheSettings['name'] === $eventNameToSearchFor
                && isset($cacheSettings['is_cache_enabled']) 
                && !$cacheSettings['is_cache_enabled']) {
                    return false;
            }
        }

        return true;
    }

    private function getDefaultEventCacheEnabled(): bool
    {
        return $this->getParam('default_event_cache_enabled');
    }

    private function getDefaultEventBlockCacheEnabled(): bool
    {
        return $this->getParam('default_event_block_cache_enabled');
    }

    private function getDefaultEventCacheTtl(): int
    {
        return $this->getParam('default_event_cache_ttl');
    }

    private function getDefaultEventBlockCacheTtl(): int
    {
        return $this->getParam('default_event_block_cache_ttl');
    }

    public function checkEventCache(string $eventNameToSearchFor): array
    {
        $result =   [
            'cacheEnabled' => false, 
            'ttl' => 0
        ];

        if(!$this->isCacheEnabled()
            || !$this->isEventCacheEnabled($eventNameToSearchFor)) {
            return $result;
        }

        $result['cacheEnabled'] = $this->getDefaultEventCacheEnabled() && $this->isCacheEnabled();
        $result['ttl' ] = $this->getDefaultEventCacheTtl();

        $cacheableTemplateEvents = $this->getCacheableSyliusTemplateEvents();

        foreach($cacheableTemplateEvents as $cacheSettings) {
            if ($cacheSettings['name'] === $eventNameToSearchFor) {

                $defaultEventBlockCacheEnabled = $cacheSettings['default_event_block_cache_enabled'];

                if(isset($cacheSettings['is_cache_enabled'])) {
                    $result['cacheEnabled'] = $cacheSettings['is_cache_enabled'];
                }
                if(isset($cacheSettings['ttl'])) {
                    $result['ttl'] = (int)$cacheSettings['ttl'];
                }
                
                if (isset($cacheSettings['blocks'])
                    && is_array($cacheSettings['blocks'])
                    && count($cacheSettings['blocks']) > 0) {
                    foreach($cacheSettings['blocks'] as $eventBlock) {
                        if((isset($eventBlock['is_cache_enabled']) && !$eventBlock['is_cache_enabled'])
                            || (!isset($eventBlock['is_cache_enabled']) && !$defaultEventBlockCacheEnabled)) {
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
        string $blockNameToSearchFor): array
    {
        $result =   [
            'cacheEnabled' => false, 
            'ttl' => 0
        ];

        if(!$this->isCacheEnabled()
            || !$this->isEventCacheEnabled($eventNameToSearchFor)) {
            return $result;
        }

        $result['cacheEnabled'] = $this->getDefaultEventBlockCacheEnabled() && $this->isCacheEnabled();
        $result['ttl' ] = $this->getDefaultEventBlockCacheTtl();

        $cacheableTemplateEvents = $this->getCacheableSyliusTemplateEvents();

        foreach($cacheableTemplateEvents as $cacheSettings) {
            if ($cacheSettings['name'] === $eventNameToSearchFor) {
                if(isset($cacheSettings['default_event_block_cache_enabled'])) {
                    $result['cacheEnabled'] = $cacheSettings['default_event_block_cache_enabled'];
                }
                if(isset($cacheSettings['default_event_block_cache_ttl'])) {
                    $result['ttl'] = (int)$cacheSettings['default_event_block_cache_ttl'];
                }

                if(isset($cacheSettings['blocks'])
                    && is_array($cacheSettings['blocks'])
                    && count($cacheSettings['blocks']) > 0) {
                    foreach($cacheSettings['blocks'] as $eventBlock) {
                        if ($eventBlock['name'] === $blockNameToSearchFor) {
                            if(isset($eventBlock['is_cache_enabled'])) {
                                $result['cacheEnabled'] = $eventBlock['is_cache_enabled'];
                            }
                            if(isset($eventBlock['ttl'])) {
                                $result['ttl'] = $eventBlock['ttl'];
                            }
                        }
                    }
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

    public function shouldUseCache(array $result): bool {
        if($result['cacheEnabled'] && $result['ttl'] > 0) {
            return true;
        }
        return false;
    }
}
