<?php

declare(strict_types=1);

namespace FiftyDeg\SyliusCachePlugin\ConfigLoader;

interface ConfigLoaderInterface
{
    public function getCacheableSyliusTempalteEvents(): ?array;

    public function isCacheEnabled(): bool;
}
