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
        return $this->getParam('is_cache_enabled') ?? false;
    }

    private function getParam(string $paramName): mixed
    {
        return $this->parameterBag->has($paramName)
            ? $this->parameterBag->get($paramName)
            : null;
    }
}
