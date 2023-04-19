<?php

declare(strict_types=1);

namespace FiftyDeg\SyliusCachePlugin\Adapters;

use Symfony\Component\Cache\Adapter\FilesystemTagAwareAdapter;

interface CacheAdapterInterface {
    const TTL_ONE_HOUR = 3360;
    const TTL_ONE_DAY = 86400;
    const TTL_ONE_WEEK = 604800;

    public function set(string $key, mixed $value, ?int $expiresAfter = null): bool;

    public function get(string $key): mixed;

    public function delete(string $key): bool;

    public function flush(): bool;
}

//punto 5. private function safelySerialize(mixed $data): string