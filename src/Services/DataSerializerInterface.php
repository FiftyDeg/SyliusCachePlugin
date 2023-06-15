<?php

declare(strict_types=1);

namespace FiftyDeg\SyliusCachePlugin\Services;

interface DataSerializerInterface
{
    /**
     * @param array<mixed>|string|int $data
     */
    public function safelySerialize($data): string;
}
