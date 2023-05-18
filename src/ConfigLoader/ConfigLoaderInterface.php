<?php

declare(strict_types=1);

namespace FiftyDeg\SyliusCachePlugin\ConfigLoader;

interface ConfigLoaderInterface
{
    public function getCacheableSyliusTemplateEvents(): ?array;

    public function isCacheEnabled(): bool;

    public function getEventCacheTTL(string $eventNameToSearchFor): int;

    public function getBlockCacheTTL(string $eventNameToSearchFor, string $blockNameToSearchFor): int;
}
