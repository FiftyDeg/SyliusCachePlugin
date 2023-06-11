<?php

declare(strict_types=1);

namespace FiftyDeg\SyliusCachePlugin\Renderer;

use FiftyDeg\SyliusCachePlugin\Adapters\CacheAdapterInterface;
use FiftyDeg\SyliusCachePlugin\ConfigLoader\ConfigLoaderInterface;
use Sylius\Bundle\UiBundle\ContextProvider\ContextProviderInterface;
use Sylius\Bundle\UiBundle\Registry\TemplateBlock;
use Sylius\Bundle\UiBundle\Renderer\TemplateBlockRendererInterface;
use Twig\Environment;

final class TwigTemplateBlockRenderer implements TemplateBlockRendererInterface
{
    public function __construct(
        private Environment $twig,
        private CacheAdapterInterface $cacheAdapter,
        private ConfigLoaderInterface $configLoader,
        private iterable $contextProviders,
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

        foreach ($this->contextProviders as $contextProvider) {
            if (!$contextProvider instanceof ContextProviderInterface || !$contextProvider->supports($templateBlock)) {
                continue;
            }

            $context = $contextProvider->provide($context, $templateBlock);
        }

        $renderedBlock = $this->twig->render($templateBlock->getTemplate(), $context);

        return $rendererUtilities->saveInCacheAndPrintOutputData(
            $this->cacheAdapter,
            $cacheKey,
            $renderedBlock,
            $blockCacheTTL,
            $templateBlock,
        );
    }
}
