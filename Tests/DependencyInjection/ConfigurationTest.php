<?php

namespace ServerGrove\LocaleBundle\Tests\DependencyInjection;

use ServerGrove\LocaleBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;

/**
 * Class ConfigurationTest
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class ConfigurationTest extends \PHPUnit_Framework_TestCase
{

    public function testDefaultConfiguration()
    {
        $config = $this->getConfigFromExtension(array());

        $this->assertInternalType('array', $config);
        $this->assertCount(7, $config);

        $this->assertArrayHasKey('flags_path', $config);
        $this->assertInternalType('string', $config['flags_path']);
        $this->assertNotEmpty($config['flags_path']);

        $this->assertArrayHasKey('enabled_locales', $config);
        $this->assertInternalType('array', $config['enabled_locales']);
        $this->assertEmpty($config['enabled_locales']);

        $this->assertArrayHasKey('hide_current_locale', $config);
        $this->assertInternalType('boolean', $config['hide_current_locale']);

        $this->assertArrayHasKey('cache_warmer', $config);
        $this->assertInternalType('array', $config['cache_warmer']);
        $this->assertNotEmpty($config['cache_warmer']);

        $this->assertArrayHasKey('template', $config);
        $this->assertInternalType('string', $config['template']);
        $this->assertNotEmpty($config['template']);

        $this->assertArrayHasKey('loader', $config);
        $this->assertInternalType('array', $config['loader']);
        $this->assertNotEmpty($config['loader']);

        $this->assertArrayHasKey('domains', $config);
        $this->assertInternalType('array', $config['domains']);
        $this->assertEmpty($config['domains']);
    }

    private function getConfigFromExtension(array $configs)
    {
        $container = new \Symfony\Component\DependencyInjection\ContainerBuilder();

        $configuration = new Configuration();
        $processor     = new Processor();
        $config        = $processor->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../../Resources/config'));
        $loader->load('services.xml');

        return $config;
    }
}
