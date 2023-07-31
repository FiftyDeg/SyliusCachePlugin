<?php

declare(strict_types=1);

namespace FiftyDeg\SyliusCachePlugin\Renderer;

use FiftyDeg\SyliusCachePlugin\Adapters\CacheAdapterInterface;
use FiftyDeg\SyliusCachePlugin\ConfigLoader\ConfigLoaderInterface;
use FiftyDeg\SyliusCachePlugin\Services\DataSerializerInterface;
use Sylius\Bundle\CoreBundle\SyliusCoreBundle;
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
        private DataSerializerInterface $dataSerializer,
        private iterable $contextProviders,
    ) {
    }

    public function render(TemplateBlock $templateBlock, array $context = []): string
    {
        $cacheTtl = $this->configLoader->getBlockCacheTtl($templateBlock->getEventName(), $templateBlock->getName());
        $cacheKey = $this->dataSerializer->safelySerialize([$templateBlock->getEventName(), $templateBlock->getName(), $context, $cacheTtl]);

        /** @var string|null $cacheValue */
        $cacheValue = $this->cacheAdapter->get($cacheKey);

        if (null !== $cacheValue) {
            return $cacheValue;
        }

        if (strcmp(SyliusCoreBundle::VERSION_ID, '112') >= 0 && interface_exists(ContextProviderInterface::class)) {
            // Sylius v1.12
            foreach ($this->contextProviders as $contextProvider) {
                if (!$contextProvider instanceof ContextProviderInterface || !$contextProvider->supports($templateBlock)) {
                    continue;
                }

                $context = $contextProvider->provide($context, $templateBlock);
            }
        } else {
            // Sylius v1.11 backward compatibility
            $context = array_replace($templateBlock->getContext(), $context);
        }

        $renderedBlock = $this->twig->render($templateBlock->getTemplate(), $context);

        if ($cacheTtl > 0) {
            $this->cacheAdapter->set($cacheKey, $renderedBlock, $cacheTtl);
        }

        return $renderedBlock;
    }
}
