<?php

declare(strict_types=1);

namespace FiftyDeg\SyliusCachePlugin\Services;

use Sylius\Bundle\ResourceBundle\Controller\RequestConfiguration;
use Symfony\Bridge\Twig\AppVariable;

final class DataSerializer implements DataSerializerInterface
{
    /**
     * @param array<mixed>|string|int $data
     */
    public function safelySerialize(mixed $data): string
    {
        if (is_string($data) || is_numeric($data)) {
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
}
