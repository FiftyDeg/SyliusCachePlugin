<?php

/*
 * This file is part of the FiftyDeg Sylius Cache Plugin package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FiftyDeg\SyliusCachePlugin\Twig;

use FiftyDeg\SyliusCachePlugin\Adapters\CacheAdapterInterface;
use FiftyDeg\SyliusCachePlugin\ConfigLoader\ConfigLoaderInterface;
use FiftyDeg\SyliusCachePlugin\Services\DataSerializerInterface;
use Sylius\Bundle\UiBundle\Renderer\TemplateEventRendererInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @experimental
 */
final class TemplateEventExtension extends AbstractExtension
{
    public function __construct(
        private TemplateEventRendererInterface $templateRenderer,
        private CacheAdapterInterface $cacheAdapter,
        private ConfigLoaderInterface $configLoader,
        private DataSerializerInterface $dataSerializer,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('sylius_template_event', [$this, 'render'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @param string|string[] $eventName
     */
    public function render($eventName, array $context = [], int $cacheTtl = -1): string
    {
        if (is_string($eventName)) {
            $eventCacheTTL = $this->configLoader->getEventCacheTTL($eventName);

            if ($cacheTtl === -1) {
                $cacheTtl = $eventCacheTTL;
            }
        }

        if ($cacheTtl <= 0) {
            return $this->doRender($eventName, $context);
        }

        $cacheKey = $this->dataSerializer->buildCacheKey($eventName, $context, $cacheTtl);

        /** @var string|null $cacheValue */
        $cacheValue = $this->cacheAdapter->get($cacheKey);

        if (null !== $cacheValue &&
            $cacheValue !== '') {
            return $cacheValue;
        }

        $renderedHtml = $this->doRender($eventName, $context);

        $this->cacheAdapter->set($cacheKey, $renderedHtml, $cacheTtl);

        return $renderedHtml;
    }

    private function doRender(string|array $eventName, array $context = []): string
    {   /**
        * @psalm-var non-empty-list<string> $eventNames
        */
        $eventNames = is_array($eventName)
            ? $eventName
            : [$eventName];

        return $this->templateRenderer->render($eventNames, $context);
    }
}
