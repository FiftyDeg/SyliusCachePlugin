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
use Exception;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfiguration;
use Sylius\Bundle\UiBundle\Renderer\TemplateEventRendererInterface;
use Symfony\Bridge\Twig\AppVariable;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @experimental
 */
final class TemplateEventExtension extends AbstractExtension
{
    public function __construct(
        private TemplateEventRendererInterface $templateEventRenderer,
        private CacheAdapterInterface $cacheAdapter,
        private ConfigLoaderInterface $configLoader,
    )
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('sylius_template_event', [$this, 'render'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @param string|string[] $eventName
     * @param array $context
     * @param int $cacheTtl
     */
    public function render(string|array $eventName, array $context = [], int $cacheTtl = 0): string
    {
        if (is_string($eventName) && empty($cacheTtl)) {
            $cacheTtl = $this->getTemplateEventCacheTtl($eventName);
        }

        if (empty($cacheTtl)) {
            return $this->doRender($eventName, $context);
        }

        $cacheKey = $this->safelySerialize($eventName) . $this->safelySerialize($context) . $cacheTtl;

        $cacheValue = $this->cacheAdapter->get($cacheKey);

        if (!is_null($cacheValue)) {
            return $cacheValue;
        }

        $renderedHtml = $this->doRender($eventName, $context);

        $this->cacheAdapter->set($cacheKey, $renderedHtml, $cacheTtl);

        return $renderedHtml;
    }

    private function getTemplateEventCacheTtl(string $eventName): int
    {
        $cacheableTemplateEvents = $this->configLoader->getCacheableSyliusTempalteEvents();

        foreach($cacheableTemplateEvents as $cacheSettings) {
            if ($cacheSettings['name'] === $eventName) {
                return (int) $cacheSettings['ttl'];
            }
        }

        return 0;
    }

    private function doRender(string|array $eventName, array $context = []): string
    {   /**
        * @psalm-var non-empty-list<string> $eventNames
        */
        $eventNames = is_array($eventName)
            ? $eventName
            : [$eventName];

        return $this->templateEventRenderer->render($eventNames, $context);
    }

    private function safelySerialize(mixed $data): string
    {
        if (is_string($data)) {
            return serialize($data);
        }

        $serializable = [];

        foreach ($data as $key => $val) {
            if ($val instanceof RequestConfiguration) {
                $serializable[$key] = $val->getRequest()->getPathInfo();

                continue;
            }

            if ($val instanceof AppVariable) {
                $serializable[$key] = $val->getEnvironment();

                continue;
            }

            else {
                try {
                    $serializable[$key] = serialize($val);
                } catch (Exception $e) {
                    // This is a workaround to create a (non safe) cache key for data containing closures
                    $serializable[$key] = $key . "-non-serializable";
                }
            }
        }

        return serialize($serializable);
    }
}
