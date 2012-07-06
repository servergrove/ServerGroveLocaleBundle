<?php

namespace ServerGrove\LocaleBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Definition;

/**
 * Class CacheWarmerDefinition
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class CacheWarmerDefinition extends Definition
{

    /**
     * Constructor
     *
     * @param string $flagsPath
     * @param array  $patterns
     * @param array  $defaults
     */
    public function __construct($flagsPath, array $patterns, array $defaults)
    {
        parent::__construct();

        $this
            ->setClass('%server_grove_locale.flag_cache_warmer.class%')
            ->setPublic(false)
            ->addArgument('%kernel.root_dir%')
            ->addArgument($flagsPath)
            ->addArgument($patterns)
            ->addArgument($defaults)
            ->addArgument('%server_grove_locale.enabled_locales%')
            ->addTag('kernel.cache_warmer');
    }

}
