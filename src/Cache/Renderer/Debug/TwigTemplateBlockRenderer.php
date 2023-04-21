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

namespace FiftyDeg\SyliusCachePlugin\Cache\Renderer\Debug;

use Sylius\Bundle\UiBundle\ContextProvider\ContextProviderInterface;
use Sylius\Bundle\UiBundle\Registry\TemplateBlock;
use Twig\Environment;
use FiftyDeg\SyliusCachePlugin\Adapters\CacheAdapterInterface;
use FiftyDeg\SyliusCachePlugin\Cache\Services\DataSerializer;
use FiftyDeg\SyliusCachePlugin\ConfigLoader\ConfigLoaderInterface;
use Sylius\Bundle\UiBundle\Renderer\TemplateBlockRendererInterface;
use Sylius\Bundle\UiBundle\DataCollector\TemplateBlockRenderingHistory;

final class TwigTemplateBlockRenderer implements TemplateBlockRendererInterface
{
    public function __construct(private Environment $twig, 
        private CacheAdapterInterface $fileSystemCacheAdapter,
        private ConfigLoaderInterface $configLoader,
        private TemplateBlockRenderingHistory $templateBlockRenderingHistory,
        private TemplateBlockRendererInterface $templateBlockRenderer )
    {
    }

    public function render(TemplateBlock $templateBlock, array $context = []): string
    {
        $dataSerializer = new DataSerializer();

        $shouldUseCache = false;
        if (is_string($templateBlock->getName())) {
            $checkEventBlockCache = $this->configLoader->checkEventBlockCache($templateBlock->getEventName(), $templateBlock->getName());
            $shouldUseCache = $this->configLoader->shouldUseCache($checkEventBlockCache);
        }

        $cacheKey = $dataSerializer->buildCacheKey($templateBlock->getName(), $context, $checkEventBlockCache['ttl']);

        if($shouldUseCache) {
            $blockFromCache = $this->fileSystemCacheAdapter->get($cacheKey, true);
            if(!is_null($blockFromCache)) {
                return $blockFromCache;
            }
        }

        $this->templateBlockRenderingHistory->startRenderingBlock($templateBlock, $context);
        $renderedBlock = $this->templateBlockRenderer->render($templateBlock, $context);
        $this->templateBlockRenderingHistory->stopRenderingBlock($templateBlock, $context);

        if($shouldUseCache) {
            $this->fileSystemCacheAdapter->set($cacheKey, $renderedBlock);
        }

        $debugString = 'event name: ' . $templateBlock->getEventName() . ', block name: ' . $templateBlock->getName() . ', template: "%s", priority: %d -->';
        return '<!-- 222 FIFTYDEG SYLIUS BLOCK CACHE PLUGIN BEGIN BLOCK | ' . $debugString . 
                    $renderedBlock .
                    '<!-- FIFTYDEG SYLIUS BLOCK CACHE PLUGIN END BLOCK | ' . $debugString;
    }

    private function buildKey($dataSerializer, $blockName, $context, $cacheTtl) {
        return $dataSerializer->safelySerialize($blockName) . $dataSerializer->safelySerialize($context) . $cacheTtl;
    }
}
