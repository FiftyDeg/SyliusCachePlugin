<?php

declare(strict_types=1);

namespace FiftyDeg\SyliusCachePlugin;

use Sylius\Bundle\CoreBundle\Application\SyliusPluginTrait;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class FiftyDegSyliusCachePlugin extends Bundle
{
    use SyliusPluginTrait;
}
