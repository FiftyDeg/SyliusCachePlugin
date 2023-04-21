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

use Sylius\Bundle\UiBundle\ContextProvider\ContextProviderInterface;
use Sylius\Bundle\UiBundle\Registry\TemplateBlock;
use Twig\Environment;
use FiftyDeg\SyliusCachePlugin\Adapters\CacheAdapterInterface;
use FiftyDeg\SyliusCachePlugin\Cache\Services\DataSerializer;
use FiftyDeg\SyliusCachePlugin\ConfigLoader\ConfigLoaderInterface;
use Sylius\Bundle\UiBundle\Renderer\TemplateBlockRendererInterface;
use Sylius\Bundle\UiBundle\DataCollector\TemplateBlockRenderingHistory;

final class TwigTemplateBlockRendererUtilities
{
    public function __construct()
    {
    }

    public function checkCacheForBlock(
        ConfigLoaderInterface $configLoader, 
        CacheAdapterInterface $fileSystemCacheAdapter, 
        TemplateBlock $templateBlock, 
        array $context = []): array
    {
        $dataSerializer = new DataSerializer();

        $shouldUseCache = false;
        if (is_string($templateBlock->getName())) {
            $checkEventBlockCache = $configLoader->checkEventBlockCache($templateBlock->getEventName(), $templateBlock->getName());
            $shouldUseCache = $checkEventBlockCache['shouldUseCache'];
        }

        $cacheKey = $dataSerializer->buildCacheKey($templateBlock->getName(), $context, $checkEventBlockCache['ttl']);

        $blockFromCache = null;
        if($shouldUseCache) {
            $blockFromCache = $fileSystemCacheAdapter->get($cacheKey, true);
        }

        return  [
                    'checkEventBlockCache' => $checkEventBlockCache,
                    'shouldUseCache' => $shouldUseCache,
                    'blockFromCache' => $blockFromCache,
                    'cacheKey' => $cacheKey
                ];
    }

    public function saveInCacheAndPrintOutputData($shouldUseCache, $fileSystemCacheAdapter, $cacheKey, $renderedBlock, $checkEventBlockCache, $templateBlock) : string {
        if($shouldUseCache) {
            $fileSystemCacheAdapter->set($cacheKey, $renderedBlock, $checkEventBlockCache['ttl']);
        }

        $debugString = 'event name: ' . $templateBlock->getEventName() . ', block name: ' . $templateBlock->getName() . ' -->';
        return '<!-- FIFTYDEG SYLIUS BLOCK CACHE PLUGIN BEGIN BLOCK | ' . $debugString . 
                    $renderedBlock .
                    '<!-- FIFTYDEG SYLIUS BLOCK CACHE PLUGIN END BLOCK | ' . $debugString;
    }
}
