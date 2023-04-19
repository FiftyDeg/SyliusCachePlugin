<?php

declare(strict_types=1);

namespace FiftyDeg\SyliusCachePlugin\ConfigLoader;

interface ConfigLoaderInterface
{
    public function getCacheableSyliusTemplateEvents(): ?array;

    public function isCacheEnabled(): bool;
}
