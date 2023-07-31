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
        $eventNames = (array) $eventName;
        $renderedParts = [];

        foreach ($eventNames as $event) {
            if ($cacheTtl === -1) {
                $eventCacheTtl = $this->configLoader->getEventCacheTtl($event);
                $cacheTtl = $eventCacheTtl;
            }

            if ($cacheTtl <= 0) {
                $renderedParts[] = $this->templateRenderer->render((array) $event, $context);

                continue;
            }

            $cacheKey = $this->dataSerializer->safelySerialize([$event, $context, $cacheTtl]);

            /** @var string|null $cacheValue */
            $cacheValue = $this->cacheAdapter->get($cacheKey);

            if (null !== $cacheValue && '' !== $cacheValue) {
                $renderedParts[] = $cacheValue;

                continue;
            }

            $renderedHtml = $this->templateRenderer->render((array) $event, $context);
            $this->cacheAdapter->set($cacheKey, $renderedHtml, $cacheTtl);

            $renderedParts[] = $renderedHtml;
        }

        return implode("\n", $renderedParts);
    }
}
