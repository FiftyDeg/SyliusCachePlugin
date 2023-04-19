<?php

declare(strict_types=1);

namespace FiftyDeg\SyliusCachePlugin\FiftyDeg\Cache\Services;

interface DataSerializerInterface
{
    public function safelySerialize(mixed $data): string;
}
