<?php

declare(strict_types=1);

namespace FiftyDeg\SyliusCachePlugin\Cache\Services;

use Sylius\Bundle\ResourceBundle\Controller\RequestConfiguration;
use Symfony\Bridge\Twig\AppVariable;

interface DataSerializerInterface
{
    /**
     * @param array<string, AppVariable|RequestConfiguration|string>|string $data
     */
    public function safelySerialize($data): string;
}
