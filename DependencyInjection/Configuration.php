<?php

namespace ServerGrove\LocaleBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode    = $treeBuilder->root('server_grove_locale');

        $this->configureFlagsPath($rootNode);
        $this->configureHiddenLocales($rootNode);
        $this->configureCacheWarmer($rootNode);
        $this->configureTemplate($rootNode);
        $this->configureLoader($rootNode);
        $this->configureDomains($rootNode);

        return $treeBuilder;
    }

    /**
     * @param ArrayNodeDefinition|NodeDefinition $rootNode
     */
    private function configureFlagsPath($rootNode)
    {
        $rootNode
            ->children()
                ->scalarNode('flags_path')
                    ->cannotBeEmpty()
                    ->defaultValue(dirname(__DIR__).'/Resources/public/images')
                ->end()
            ->end();
    }

    /**
     * @param ArrayNodeDefinition|NodeDefinition $rootNode
     */
    private function configureHiddenLocales($rootNode)
    {
        $rootNode
            ->children()
                ->scalarNode('hide_current_locale')
                    ->cannotBeEmpty()
                    ->treatNullLike(true)
                    ->defaultValue(true)
                ->end()
            ->end();
    }

    /**
     * @param ArrayNodeDefinition|NodeDefinition $rootNode
     */
    private function configureCacheWarmer($rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('cache_warmer')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('enabled')->defaultTrue()->end()

                        ->arrayNode('patterns')
                            ->addDefaultsIfNotSet()
                            ->prototype('scalar')
                                ->validate()
                                    ->ifString(function($pattern) {
                                        return false !== strpos($pattern, '?P<locale>');
                                    })
                                    ->thenInvalid('Pattern must include a "lang" mark')
                                ->end()
                            ->end()
                            ->defaultValue(array(
                                '/^(?P<locale>[a-z]{2}).png$/',
                                '/^(?P<locale>[a-z]{2})\-(?P<country>[A-Z]{2}).png$/'
                            ))
                        ->end()

                        ->arrayNode('defaults')
                            ->beforeNormalization()
                                ->always()
                                ->then(function($defaults){
                                    // This allows to receive an associative array and not an numeric-indexed array
                                    return $defaults;
                                })
                            ->end()
                            ->useAttributeAsKey('lang')
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * @param ArrayNodeDefinition|NodeDefinition $rootNode
     */
    private function configureTemplate($rootNode)
    {
        $rootNode
            ->children()
                ->scalarNode('template')
                    ->cannotBeEmpty()
                    ->defaultValue('ServerGroveLocaleBundle::flags.html.twig')
                ->end()
            ->end();
    }

    /**
     * @param ArrayNodeDefinition|NodeDefinition $rootNode
     */
    private function configureLoader($rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('loader')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('class')
                            ->cannotBeEmpty()
                            ->defaultValue('ServerGrove\LocaleBundle\Flag\CacheLoader')
                            ->validate()
                                ->ifTrue(function($class){
                                    return in_array('ServerGrove\LocaleBundle\Flag\LoaderInterface', class_implements($class));
                                })
                                ->thenInvalid('Loader class must implement the interface "ServerGrove\LocaleBundle\Flag\LoaderInterface"')
                            ->end()
                        ->end()

                        ->arrayNode('arguments')
                            ->addDefaultsIfNotSet()
                            ->defaultValue(array("%kernel.cache_dir%"))
                            ->prototype('variable')
                                ->treatNullLike(array())
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * @param ArrayNodeDefinition $rootNode
     */
    private function configureDomains($rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('domains')
                    ->beforeNormalization()
                        ->always()
                        ->then(function($domains){
                            $result = array();
                            foreach ($domains as $domain) {
                                $schema = isset($domain['schema']) ? $domain['schema'] : 'http';
                                $result[$domain['locale']] = sprintf('%s://%s', $schema, $domain['domain']);
                                if (isset($domain['default']) && $domain['default']) {
                                    $result['default'] = $result[$domain['locale']];
                                }
                            }

                            return $result;
                        })
                    ->end()
                    ->useAttributeAsKey('locale')
                    ->prototype('scalar')->end()
                ->end()
            ->end();
    }
}
