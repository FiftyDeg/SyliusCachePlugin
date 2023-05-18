<?php

declare(strict_types=1);

namespace FiftyDeg\SyliusCachePlugin\Renderer\Debug;

use FiftyDeg\SyliusCachePlugin\Adapters\CacheAdapterInterface;
use FiftyDeg\SyliusCachePlugin\ConfigLoader\ConfigLoaderInterface;
use FiftyDeg\SyliusCachePlugin\Renderer\TwigTemplateBlockRendererUtilities;
use Sylius\Bundle\UiBundle\DataCollector\TemplateBlockRenderingHistory;
use Sylius\Bundle\UiBundle\Registry\TemplateBlock;
use Sylius\Bundle\UiBundle\Renderer\TemplateBlockRendererInterface;

final class TwigTemplateBlockRenderer implements TemplateBlockRendererInterface
{
    public function __construct(
        private CacheAdapterInterface $cacheAdapter,
        private ConfigLoaderInterface $configLoader,
        private TemplateBlockRenderingHistory $blockRenderHistory,
        private TemplateBlockRendererInterface $blockRenderer,
    ) {
    }

    public function render(TemplateBlock $templateBlock, array $context = []): string
    {
        $rendererUtilities = new TwigTemplateBlockRendererUtilities($this->configLoader, $this->cacheAdapter);
        $cacheForBlock = $rendererUtilities->getCacheForBlock($templateBlock, $context);

        /** @var int $blockCacheTTL */
        $blockCacheTTL = $cacheForBlock['blockCacheTTL'];

        /** @var string|null $blockFromCache */
        $blockFromCache = $cacheForBlock['blockFromCache'];

        /** @var string $cacheKey */
        $cacheKey = $cacheForBlock['cacheKey'];

        if (null !== $blockFromCache) {
            return $blockFromCache;
        }

        $this->blockRenderHistory->startRenderingBlock($templateBlock, $context);
        $renderedBlock = $this->blockRenderer->render($templateBlock, $context);
        $this->blockRenderHistory->stopRenderingBlock($templateBlock, $context);

        return $rendererUtilities->saveInCacheAndPrintOutputData(
            $this->cacheAdapter,
            $cacheKey,
            $renderedBlock,
            $blockCacheTTL,
            $templateBlock,
        );
    }
}
