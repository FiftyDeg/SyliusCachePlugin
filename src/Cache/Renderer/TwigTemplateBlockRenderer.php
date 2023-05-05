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
use FiftyDeg\SyliusCachePlugin\ConfigLoader\ConfigLoaderInterface;
use Sylius\Bundle\UiBundle\ContextProvider\ContextProviderInterface;
use Sylius\Bundle\UiBundle\Registry\TemplateBlock;
use Sylius\Bundle\UiBundle\Renderer\TemplateBlockRendererInterface;
use Twig\Environment;

final class TwigTemplateBlockRenderer implements TemplateBlockRendererInterface
{
    public function __construct(
        private Environment $twig,
        private CacheAdapterInterface $fsCacheAdapter,
        private ConfigLoaderInterface $configLoader,
        private iterable $contextProviders,
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

        /** @var array<mixed, mixed> $contextProviders */
        $contextProviders = $this->contextProviders;
        /** @var ContextProviderInterface $contextProvider */
        foreach ($contextProviders as $contextProvider) {
            /** @var string $classToCheck */
            $classToCheck = 'Sylius\Bundle\UiBundle\ContextProvider\ContextProviderInterface';
            if ((!$contextProvider instanceof $classToCheck) ||
                !$contextProvider->supports($templateBlock)) {
                continue;
            }

            $context = $contextProvider->provide($context, $templateBlock);
        }
        $renderedBlock = $this->twig->render($templateBlock->getTemplate(), $context);

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
