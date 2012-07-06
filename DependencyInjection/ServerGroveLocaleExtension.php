<?php

namespace ServerGrove\LocaleBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class ServerGroveLocaleExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config        = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $container->setParameter('server_grove_locale.hide_current_locale', $config['hide_current_locale']);
        $container->setParameter('server_grove_locale.enabled_locales', $config['enabled_locales']);
        $container->setParameter('server_grove_locale.template', $config['template']);
        $container->setParameter('server_grove_locale.flags_path', $config['flags_path']);

        if (count($config['domains']) > 0 && !isset($config['domains']['default'])) {
            throw new \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException('A default domain must be configured');
        }
        $container->setParameter('server_grove_locale.domains', $config['domains']);

        $container->setParameter('server_grove_locale.flag.loader.class', $config['loader']['class']);
        foreach ($config['loader']['arguments'] as $argument) {
            $container->getDefinition('server_grove_locale.flag.loader')->addArgument($argument);
        }

        if ($config['cache_warmer']['enabled']) {
            $container->setDefinition('server_grove_locale.flag_cache_warmer', new CacheWarmerDefinition(
                $config['flags_path'],
                $config['cache_warmer']['patterns'],
                $config['cache_warmer']['defaults']
            ));
        }
    }
}
