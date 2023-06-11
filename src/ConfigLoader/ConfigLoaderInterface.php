<?php

declare(strict_types=1);

namespace FiftyDeg\SyliusCachePlugin\ConfigLoader;

interface ConfigLoaderInterface
{
    public function getCacheableTemplateEvents(): ?array;

    public function isCacheEnabled(): bool;

    public function getEventCacheTtl(string $eventName): int;

    public function getBlockCacheTTL(string $eventName, string $blockName): int;
}
