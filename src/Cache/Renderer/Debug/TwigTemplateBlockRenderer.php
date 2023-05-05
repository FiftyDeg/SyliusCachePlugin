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

use FiftyDeg\SyliusCachePlugin\Adapters\CacheAdapterInterface;
use FiftyDeg\SyliusCachePlugin\Cache\Renderer\TwigTemplateBlockRendererUtilities;
use FiftyDeg\SyliusCachePlugin\ConfigLoader\ConfigLoaderInterface;
use Sylius\Bundle\UiBundle\DataCollector\TemplateBlockRenderingHistory;
use Sylius\Bundle\UiBundle\Registry\TemplateBlock;
use Sylius\Bundle\UiBundle\Renderer\TemplateBlockRendererInterface;

final class TwigTemplateBlockRenderer implements TemplateBlockRendererInterface
{
    public function __construct(
        private CacheAdapterInterface $fsCacheAdapter,
        private ConfigLoaderInterface $configLoader,
        private TemplateBlockRenderingHistory $blockRenderHistory,
        private TemplateBlockRendererInterface $blockRenderer,
    ) {
    }

    public function render(TemplateBlock $templateBlock, array $context = []): string
    {
        $rendererUtilities = new TwigTemplateBlockRendererUtilities();
        $checkCacheForBlock = $rendererUtilities->checkCacheForBlock($this->configLoader, $this->fsCacheAdapter, $templateBlock, $context);

        /** @var array<array-key, int> $checkEventBlockCache */
        $checkEventBlockCache = $checkCacheForBlock['checkEventBlockCache'];

        /** @var bool $shouldUseCache */
        $shouldUseCache = $checkCacheForBlock['shouldUseCache'];

        /** @var string|null $blockFromCache */
        $blockFromCache = $checkCacheForBlock['blockFromCache'];

        /** @var string $cacheKey */
        $cacheKey = $checkCacheForBlock['cacheKey'];

        if (null !== $blockFromCache) {
            return $blockFromCache;
        }

        $this->blockRenderHistory->startRenderingBlock($templateBlock, $context);
        $renderedBlock = $this->blockRenderer->render($templateBlock, $context);
        $this->blockRenderHistory->stopRenderingBlock($templateBlock, $context);

        return $rendererUtilities->saveInCacheAndPrintOutputData(
            $shouldUseCache,
            $this->fsCacheAdapter,
            $cacheKey,
            $renderedBlock,
            $checkEventBlockCache,
            $templateBlock,
        );
    }
}
