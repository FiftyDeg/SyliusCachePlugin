<?php

declare(strict_types=1);

namespace FiftyDeg\SyliusCachePlugin\Cache\Services;

final class DataSerializer implements DataSerializerInterface
{
    public function __construct(
    )
    {
    }


    public function safelySerialize(mixed $data): string
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

    public function buildCacheKey($nameToUse, $context, $cacheTtl) {
        return $this->safelySerialize($nameToUse) . $this->safelySerialize($context) . $cacheTtl;
    }
}
