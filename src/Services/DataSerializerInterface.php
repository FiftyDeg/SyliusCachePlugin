<?php

declare(strict_types=1);

namespace FiftyDeg\SyliusCachePlugin\Services;

use Sylius\Bundle\ResourceBundle\Controller\RequestConfiguration;
use Symfony\Bridge\Twig\AppVariable;

interface DataSerializerInterface
{
    /**
     * @param array<string, AppVariable|RequestConfiguration|string>|string $data
     */
    public function safelySerialize($data): string;

    /**
     * @param array<array-key, AppVariable|RequestConfiguration|string>|string $nameToUse
     * @param array $context
     * @param int $cacheTtl
     */
    public function buildCacheKey($nameToUse, $context, $cacheTtl): string;
}
