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
use FiftyDeg\SyliusCachePlugin\Cache\Renderer\TwigTemplateBlockRendererUtilities;

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
        $twigTemplateBlockRendererUtilities = new TwigTemplateBlockRendererUtilities();
        $checkCacheForBlock = $twigTemplateBlockRendererUtilities->checkCacheForBlock($this->configLoader, $this->fileSystemCacheAdapter, $templateBlock, $context);

        $checkEventBlockCache = $checkCacheForBlock['checkEventBlockCache']; 
        $shouldUseCache = $checkCacheForBlock['shouldUseCache'];
        $blockFromCache = $checkCacheForBlock['blockFromCache'];
        $cacheKey = $checkCacheForBlock['cacheKey'];

        if(!is_null($blockFromCache)) {
            return $blockFromCache;
        }

        $this->templateBlockRenderingHistory->startRenderingBlock($templateBlock, $context);
        $renderedBlock = $this->templateBlockRenderer->render($templateBlock, $context);
        $this->templateBlockRenderingHistory->stopRenderingBlock($templateBlock, $context);

        return $twigTemplateBlockRendererUtilities->saveInCacheAndPrintOutputData(
            $shouldUseCache, 
            $this->fileSystemCacheAdapter, 
            $cacheKey, 
            $renderedBlock, 
            $checkEventBlockCache, 
            $templateBlock);
    }
}
