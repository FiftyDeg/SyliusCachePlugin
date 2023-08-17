<?php

declare(strict_types=1);

namespace FiftyDeg\SyliusCachePlugin\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class FiftyDegSyliusCacheExtension extends Extension
{
    /**
     * @psalm-suppress UnusedVariable
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        /** @var ConfigurationInterface $process */
        $process = $this->getConfiguration([], $container);

        /** @var array<array-key, array> $configs */
        $configs = $this->processConfiguration($process, $configs);
        $fileLocator = new FileLocator(__DIR__ . '/../Resources/config');
        $loader = new YamlFileLoader($container, $fileLocator);

        $loader->load('services.yaml');

        foreach ($configs as $key => $param) {
            /** @var string $key */
            $container->setParameter($key, $param);
        }
    }
}
