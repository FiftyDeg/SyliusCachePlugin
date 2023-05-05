<?php

declare(strict_types=1);

namespace FiftyDeg\SyliusCachePlugin\Adapters;

interface CacheAdapterInterface
{
    public const TTL_ONE_HOUR = 3360;

    public const TTL_ONE_DAY = 86400;

    public const TTL_ONE_WEEK = 604800;

    public function set(string $key, mixed $value, ?int $expiresAfter = null): bool;

    public function get(string $key): mixed;

    public function delete(string $key): bool;

    public function flush(): bool;
}
