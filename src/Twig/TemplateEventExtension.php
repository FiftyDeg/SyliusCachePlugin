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
use FiftyDeg\SyliusCachePlugin\Cache\Services\DataSerializer;
use FiftyDeg\SyliusCachePlugin\ConfigLoader\ConfigLoaderInterface;
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
        $dataSerializer = new DataSerializer();

        /** @var bool $eventCacheEnabled */
        $eventCacheEnabled = false;
        if (is_string($eventName)) {
            $checkEventCache = $this->configLoader->checkEventCache($eventName);

            /** @var bool $eventCacheEnabled */
            $eventCacheEnabled = $checkEventCache['cacheEnabled'];
            if ($cacheTtl === -1) {
                /** @var int $cacheTtl */
                $cacheTtl = $checkEventCache['ttl'];
            }
        }

        if (!$eventCacheEnabled || $cacheTtl <= 0) {
            return $this->doRender($eventName, $context);
        }

        $cacheKey = $dataSerializer->buildCacheKey($eventName, $context, $cacheTtl);

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
