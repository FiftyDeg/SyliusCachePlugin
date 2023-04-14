<?php

declare(strict_types=1);

namespace FiftyDeg\SyliusCachePlugin\Adapters;

use Exception;
use FiftyDeg\SyliusCachePlugin\ConfigLoader\ConfigLoaderInterface;
use Symfony\Component\Cache\Adapter\FilesystemTagAwareAdapter;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Locale\Context\LocaleContextInterface;

final class FilesystemCacheAdapter implements CacheAdapterInterface
{
    /** @var FilesystemTagAwareAdapter */
    private $cache;

    public function __construct(
        private ConfigLoaderInterface $configLoader,
        private ChannelContextInterface $channelContext,
        private LocaleContextInterface $localeContext,
        private string $projectDir,
        private string $env,
        private string $namespace,
        private int $ttl
    ) {
        $this->cache = new FilesystemTagAwareAdapter(
            // a string used as the subdirectory of the root cache directory, where cache
            // items will be stored
            $this->namespace,
            // the default lifetime (in seconds) for cache items that do not define their
            // own lifetime, with a value 0 causing items to be stored indefinitely (i.e.
            // until the files are deleted)
            $this->ttl,
            // the main cache directory (the application needs read-write permissions on it)
            // if none is specified, a directory is created inside the system temporary directory
            $this->projectDir . DIRECTORY_SEPARATOR . "var/cache/{$this->env}"
        );
    }

    public function set(string $key, mixed $value, ?int $expiresAfter = null): bool
    {
        if (!$this->isCacheEnabled()) {
            return false;
        }

        try {
            $hashedKey = $this->hashKey($key);
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
        if (!$this->isCacheEnabled()) {
            return null;
        }

        $hashedKey = $this->hashKey($key);

        $cacheItem = $this->cache->getItem($hashedKey);

        return $cacheItem->isHit()
            ? $cacheItem->get()
            : null;
    }

    public function delete(string $key): bool
    {
        $cacheItem = $this->get($key);

        if (!($cacheItem && $cacheItem->isHit())) {
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
        return $this->isCacheEnabled()
            ? $this->cache
            : null;
    }

    private function isCacheEnabled()
    {
        return $this->configLoader->isCacheEnabled();
    }

    private function hashKey(string $key): string
    {
        $channelCode = $this->channelContext->getChannel()->getCode();
        $localeCode  = $this->localeContext->getLocaleCode();

        $key = $channelCode . "__" . $localeCode . "__" . $key;

        return hash('md5', $key);
    }
}
