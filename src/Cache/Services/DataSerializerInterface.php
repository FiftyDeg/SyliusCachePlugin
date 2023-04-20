<?php

declare(strict_types=1);

namespace FiftyDeg\SyliusCachePlugin\Cache\Services;

interface DataSerializerInterface
{
    public function safelySerialize(mixed $data): string;
}
