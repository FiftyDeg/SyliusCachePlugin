<?php

declare(strict_types=1);

namespace FiftyDeg\SyliusCachePlugin\Renderer;

use FiftyDeg\SyliusCachePlugin\Adapters\CacheAdapterInterface;
use FiftyDeg\SyliusCachePlugin\ConfigLoader\ConfigLoaderInterface;
use FiftyDeg\SyliusCachePlugin\Services\DataSerializer;
use Sylius\Bundle\UiBundle\Registry\TemplateBlock;

final class TwigTemplateBlockRendererUtilities
{
    public function __construct(
        private ConfigLoaderInterface $configLoader,
        private CacheAdapterInterface $cacheAdapter,
    ) {
    }

    public function getCacheForBlock(
        TemplateBlock $templateBlock,
        array $context = [],
    ): array {
        $dataSerializer = new DataSerializer();

        $blockCacheTTL = $this->configLoader->getBlockCacheTTL($templateBlock->getEventName(), $templateBlock->getName());

        $cacheKey = $dataSerializer->buildCacheKey($templateBlock->getName(), $context, $blockCacheTTL);

        /** @var string|null $blockFromCache */
        $blockFromCache = null;
        if ($blockCacheTTL > 0) {
            /** @var mixed|null $blockFromCache */
            $blockFromCache = $this->cacheAdapter->get($cacheKey);
        }

        return  [
                    'blockCacheTTL' => $blockCacheTTL,
                    'blockFromCache' => $blockFromCache,
                    'cacheKey' => $cacheKey,
                ];
    }

    /**
     * @param CacheAdapterInterface $cacheAdapter
     * @param string $cacheKey
     * @param string $renderedBlock
     * @param int $blockCacheTTL
     * @param TemplateBlock $templateBlock
     */
    public function saveInCacheAndPrintOutputData($cacheAdapter, $cacheKey, $renderedBlock, $blockCacheTTL, $templateBlock): string
    {
        if ($blockCacheTTL > 0) {
            $cacheAdapter->set($cacheKey, $renderedBlock, $blockCacheTTL);
        }

        $eventName = $templateBlock->getEventName();
        $templateName = $templateBlock->getName();

        $debugString = 'event name: ' . $eventName . ', block name: ' . $templateName . ' -->';

        return '<!-- FIFTYDEG SYLIUS BLOCK CACHE PLUGIN BEGIN BLOCK | ' . $debugString .
                    $renderedBlock .
                    '<!-- FIFTYDEG SYLIUS BLOCK CACHE PLUGIN END BLOCK | ' . $debugString;
    }
}
