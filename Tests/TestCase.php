<?php

namespace ServerGrove\LocaleBundle\Tests;

use ServerGrove\LocaleBundle\Extension\FlagExtension;
use ServerGrove\LocaleBundle\Flag\Flag;
use Symfony\Component\DependencyInjection\Container;
use Assetic\Asset\FileAsset;

/**
 * Class
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array $options
     *
     * @return \ServerGrove\LocaleBundle\Extension\FlagExtension
     */
    protected function createExtension(array $options = array())
    {
        $options = array_merge(array('domains' => array(), 'defaults' => array()), $options);

        $extension = new FlagExtension($this->createContainer(), $this->createLoader($options['defaults']), 'flags.html.twig', $options['domains']);

        $twig = new \Twig_Environment(new \Twig_Loader_Filesystem(dirname(__DIR__).'/Resources/views'));

        $extension->initRuntime($twig);
        $twig->addExtension($extension);

        $generator = $this->getMock('Symfony\Component\Routing\Generator\UrlGeneratorInterface');
        $generator
            ->expects($this->any())
            ->method('generate')
            ->will($this->returnValue('/'));

        $routingExtension = new \Symfony\Bridge\Twig\Extension\RoutingExtension($generator);

        $twig->addExtension($routingExtension);
        $routingExtension->initRuntime($twig);

        return $extension;
    }

    /**
     * @return \Symfony\Component\DependencyInjection\Container
     */
    protected function createContainer()
    {
        $container = new Container();
        $container->set('assetic.asset_manager', $this->createAssetManager());

        return $container;
    }

    /**
     * @return \Assetic\AssetManager
     */
    protected function createAssetManager()
    {
        $manager = new \Assetic\AssetManager();

        $localeInfo = $this->getLocaleInfo();

        foreach ($localeInfo as $lang => $countries) {
            $manager->set('locale_'.$lang, $asset = new FileAsset(dirname(dirname(__DIR__)).'/Resources/public/images/'.$lang.'.png'));
            $asset->setTargetPath('images/locale/'.$lang.'.png');

            foreach ($countries as $country) {
                $manager->set('locale_'.$lang.'_'.strtolower($country), $asset = new FileAsset(dirname(dirname(__DIR__)).'/Resources/public/images/'.$lang.'-'.$country.'.png'));
                $asset->setTargetPath('images/locale/'.$lang.'-'.$country.'.png');
            }
        }

        return $manager;
    }

    /**
     * @param array $defaults
     *
     * @return \ServerGrove\LocaleBundle\Flag\LoaderInterface
     */
    protected function createLoader(array $defaults = array())
    {
        $mock = $this->getMock('ServerGrove\LocaleBundle\Flag\LoaderInterface');
        $mock->expects($this->any())->method('getFlags')->will($this->returnValue($this->getFlagsArray()));
        $mock->expects($this->any())->method('getDefaults')->will($this->returnValue($defaults));

        return $mock;
    }

    private function getFlagsArray()
    {
        $localeInfo = $this->getLocaleInfo();
        $flags      = array();

        foreach ($localeInfo as $lang => $countries) {
            $flags[$lang] = new Flag(dirname(dirname(__DIR__)).'/Resources/public/images/'.$lang.'.png', $lang);
            foreach ($countries as $country) {
                $flags[$lang] = new Flag(dirname(dirname(__DIR__)).'/Resources/public/images/'.$lang.'-'.$country.'.png', $lang, $country);
            }
        }

        return $flags;
    }

    /**
     * @return array
     */
    protected function getLocaleInfo()
    {
        return array('en' => array('GB', 'US'), 'es' => array('ES', 'AR'), 'pt' => array('PT', 'BR'));
    }
}
