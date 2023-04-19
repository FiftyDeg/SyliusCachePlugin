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

namespace FiftyDeg\SyliusCachePlugin\FiftyDeg\Cache\Renderer;

use Sylius\Bundle\UiBundle\ContextProvider\ContextProviderInterface;
use Sylius\Bundle\UiBundle\Registry\TemplateBlock;
use Twig\Environment;
use FiftyDeg\SyliusCachePlugin\Adapters\CacheAdapterInterface;
use FiftyDeg\SyliusCachePlugin\FiftyDeg\Cache\Services\DataSerializer;
use FiftyDeg\SyliusCachePlugin\ConfigLoader\ConfigLoaderInterface;

final class TwigTemplateBlockRenderer implements TemplateBlockRendererInterface
{
    public function __construct(private Environment $twig, 
        private iterable $contextProviders,
        private CacheAdapterInterface $fileSystemCacheAdapter,
        private ConfigLoaderInterface $configLoader,)
    {
        var_dump('aaaaaaaaa');
    }

    public function render(TemplateBlock $templateBlock, array $context = []): string
    {

        var_dump($configLoader);
        $dataSerializer = new DataSerializer();
        $cacheKey = $dataSerializer->safelySerialize($templateBlock);

        $blockFromCache = $fileSystemCacheAdapter->get($cacheKey);
        if(!is_null($blockFromCache)) {
            return $blockFromCache;
        }

        $renderedBlock;

        if(isset($_ENV['APP_ENV'])
            && $_ENV['APP_ENV'] == 'dev') {
            $this->templateBlockRenderingHistory->startRenderingBlock($templateBlock, $context);
            $renderedBlock = $this->templateBlockRenderer->render($templateBlock, $context);
            $this->templateBlockRenderingHistory->stopRenderingBlock($templateBlock, $context);
        }
        else {
            foreach ($this->contextProviders as $contextProvider) {
                if (!$contextProvider instanceof ContextProviderInterface || !$contextProvider->supports($templateBlock)) {
                    continue;
                }
                $context = $contextProvider->provide($context, $templateBlock);
            }
            $renderedBlock = $this->twig->render($templateBlock->getTemplate(), $context);
        }

        $fileSystemCacheAdapter->set($cacheKey, $renderedBlock);
        return '<!-- FIFTYDEG!!!!!!!!!!!!!! BEGIN BLOCK | event name: "%s", block name: "%s", template: "%s", priority: %d -->' . 
                    $renderedBlock .
                    '<!-- FIFTYDEG!!!!!!!!!!!!!!! END BLOCK | event name: "%s", block name: "%s" -->';
    }
}
