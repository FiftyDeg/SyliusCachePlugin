<?php

declare(strict_types=1);

namespace FiftyDeg\SyliusCachePlugin\Adapters;

use Exception;
use FiftyDeg\SyliusCachePlugin\Processor\Utilities;
use Symfony\Component\Cache\Adapter\FilesystemTagAwareAdapter;
use Symfony\Component\Cache\CacheItem;

final class FilesystemCacheAdapter implements CacheAdapterInterface
{
    /** @var FilesystemTagAwareAdapter */
    private $cache;

    public function __construct(
        private Utilities $utilities,
        private string $env,
        private string $cacheDir,
        private string $namespace,
        private int $defaultTtl,
    ) {
        $this->cache = new FilesystemTagAwareAdapter(
            // a string used as the subdirectory of the root cache directory, where cache
            // items will be stored
            $this->namespace,
            // the default lifetime (in seconds) for cache items that do not define their
            // own lifetime, with a value 0 causing items to be stored indefinitely (i.e.
            // until the files are deleted)
            $this->defaultTtl,
            // the main cache directory (the application needs read-write permissions on it)
            // if none is specified, a directory is created inside the system temporary directory
            $this->cacheDir . \DIRECTORY_SEPARATOR . 'var/cache/' . $this->env,
        );
    }

    public function set(string $key, mixed $value, ?int $expiresAfter = null): bool
    {
        try {
            $hashedKey = $this->utilities->hashKey($key);
            $cacheItem = $this->cache->getItem($hashedKey);

            $cacheItem->set($value);

            if (is_int($expiresAfter)) {
                $cacheItem->expiresAfter($expiresAfter);
            }

            return $this->cache->save($cacheItem);
        } catch (Exception $e) {
            return false;
        }
    }

    public function get(string $key): mixed
    {
        $hashedKey = $this->utilities->hashKey($key);

        $cacheItem = $this->cache->getItem($hashedKey);

        return $cacheItem->isHit()
            ? $cacheItem->get()
            : null;
    }

    public function delete(string $key): bool
    {
        /** @var CacheItem $cacheItem */
        $cacheItem = $this->get($key);

        if (!$cacheItem->isHit()) {
            return false;
        }

        $cacheItem->set(null);

        return $this->cache->save($cacheItem);
    }

    public function flush(): bool
    {
        return $this->cache->clear();
    }

    public function getCache(): ?FilesystemTagAwareAdapter
    {
        return $this->cache;
    }
}
