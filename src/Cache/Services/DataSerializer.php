<?php

declare(strict_types=1);

namespace FiftyDeg\SyliusCachePlugin\Cache\Services;

use Sylius\Bundle\ResourceBundle\Controller\RequestConfiguration;
use Symfony\Bridge\Twig\AppVariable;

final class DataSerializer implements DataSerializerInterface
{
    public function __construct()
    {
    }

    /**
     * @param array<mixed>|array<array-key, AppVariable|RequestConfiguration|string>|string $data
     */
    public function safelySerialize($data): string
    {
        if (is_string($data)) {
            return serialize($data);
        }

        /** @var array<mixed, mixed> $serializable */
        $serializable = [];

        /** @var AppVariable|RequestConfiguration|string $val */
        foreach ($data as $key => $val) {
            if ($val instanceof RequestConfiguration) {
                $serializable[$key] = $val->getRequest()->getPathInfo();

                continue;
            }

            if ($val instanceof AppVariable) {
                $serializable[$key] = $val->getEnvironment();

                continue;
            }

            try {
                $serializable[$key] = serialize($val);
            } catch (\Exception $e) {
                // This is a workaround to create a (non safe) cache key for data containing closures
                $serializable[$key] = $key . '-non-serializable';
            }
        }

        return serialize($serializable);
    }

    /**
     * @param array<array-key, AppVariable|RequestConfiguration|string>|string $nameToUse
     * @param array $context
     * @param int $cacheTtl
     */
    public function buildCacheKey($nameToUse, $context, $cacheTtl): string
    {
        return $this->safelySerialize($nameToUse) . $this->safelySerialize($context) . $cacheTtl;
    }
}
