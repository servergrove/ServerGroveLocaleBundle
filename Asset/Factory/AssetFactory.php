<?php

namespace ServerGrove\LocaleBundle\Asset\Factory;

use Assetic\AssetManager;
use Assetic\Factory\AssetFactory as BaseFactory;
use ServerGrove\LocaleBundle\Asset\LocaleAsset;
use ServerGrove\LocaleBundle\Flag\LoaderInterface;

/**
 * Class AssetFactory
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class AssetFactory extends BaseFactory
{

    /** @var bool */
    private $loaded;

    /** @var \ServerGrove\LocaleBundle\Flag\LoaderInterface */
    private $loader;

    /**
     * @param LoaderInterface $loader
     * @param string          $root
     * @param bool            $debug
     */
    public function __construct(LoaderInterface $loader, $root, $debug = false)
    {
        parent::__construct($root, $debug);

        $this->loader = $loader;

        $this->setAssetManager(new AssetManager());
        $this->setDefaultOutput('images/locale/*.png');

        $this->loaded = false;
    }

    public function load()
    {
        if (!$this->loaded) {
            $factory = $this;
            $flags   = $this->loader->getFlags();

            $setup = function($item, $key) use ($factory) {
                if ('defaults' != $key) {
                    $asset = $factory->createAsset($item['file'], array(), array('output' => 'images/locale/flag-'.$item['file']));
                    $key   = 'locale_'.preg_replace('/[^\w]+/', '_', $key);

                    /** @var $factory AssetFactory */
                    $factory
                        ->getAssetManager()
                        ->set($key, new LocaleAsset($asset, $item['locale'], isset($item['country']) ? $item['country'] : null));
                }
            };

            if (isset($flags['defaults'])) {
                array_walk($flags['defaults'], $setup);
            }

            $this->loaded = true;
        }
    }
}
