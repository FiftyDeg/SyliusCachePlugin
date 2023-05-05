<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FiftyDeg\SyliusCachePlugin\Cache\Renderer;

use FiftyDeg\SyliusCachePlugin\Adapters\CacheAdapterInterface;
use FiftyDeg\SyliusCachePlugin\Cache\Services\DataSerializer;
use FiftyDeg\SyliusCachePlugin\ConfigLoader\ConfigLoaderInterface;
use Sylius\Bundle\UiBundle\Registry\TemplateBlock;

final class TwigTemplateBlockRendererUtilities
{
    public function __construct()
    {
    }

    public function checkCacheForBlock(
        ConfigLoaderInterface $configLoader,
        CacheAdapterInterface $fsCacheAdapter,
        TemplateBlock $templateBlock,
        array $context = [],
    ): array {
        $dataSerializer = new DataSerializer();
        $checkEventBlockCache = $configLoader->checkEventBlockCache($templateBlock->getEventName(), $templateBlock->getName());

        /** @var bool $shouldUseCache */
        $shouldUseCache = $checkEventBlockCache['shouldUseCache'];

        /** @var int $cacheTtl */
        $cacheTtl = $checkEventBlockCache['ttl'];

        $cacheKey = $dataSerializer->buildCacheKey($templateBlock->getName(), $context, $cacheTtl);

        /** @var string|null $blockFromCache */
        $blockFromCache = null;
        if ($shouldUseCache) {
            /** @var mixed|null $blockFromCache */
            $blockFromCache = $fsCacheAdapter->get($cacheKey);
        }

        return  [
                    'checkEventBlockCache' => $checkEventBlockCache,
                    'shouldUseCache' => $shouldUseCache,
                    'blockFromCache' => $blockFromCache,
                    'cacheKey' => $cacheKey,
                ];
    }

    /**
     * @param bool $shouldUseCache
     * @param CacheAdapterInterface $fsCacheAdapter
     * @param string $cacheKey
     * @param string $renderedBlock
     * @param array<array-key, int> $checkEventBlockCache
     * @param TemplateBlock $templateBlock
     */
    public function saveInCacheAndPrintOutputData($shouldUseCache, $fsCacheAdapter, $cacheKey, $renderedBlock, $checkEventBlockCache, $templateBlock): string
    {
        if ($shouldUseCache) {
            $fsCacheAdapter->set($cacheKey, $renderedBlock, $checkEventBlockCache['ttl']);
        }

        $eventName = $templateBlock->getEventName();
        $templateName = $templateBlock->getName();

        $debugString = 'event name: ' . $eventName . ', block name: ' . $templateName . ' -->';

        return '<!-- FIFTYDEG SYLIUS BLOCK CACHE PLUGIN BEGIN BLOCK | ' . $debugString .
                    $renderedBlock .
                    '<!-- FIFTYDEG SYLIUS BLOCK CACHE PLUGIN END BLOCK | ' . $debugString;
    }
}
