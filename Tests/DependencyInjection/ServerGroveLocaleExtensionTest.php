<?php

namespace ServerGrove\LocaleBundle\Tests\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use ServerGrove\LocaleBundle\DependencyInjection\ServerGroveLocaleExtension;

/**
 * Class ServerGroveLocaleExtensionTest
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class ServerGroveLocaleExtensionTest extends \PHPUnit_Framework_TestCase
{

    public function testExtension()
    {
        $container = new ContainerBuilder();

        $extension = new ServerGroveLocaleExtension();
        $extension->load(array(), $container);

        $this->assertTrue($container->has('server_grove_locale.flag.loader'));
        $this->assertTrue($container->has('server_grove_locale.assetic.locale_loader'));
        $this->assertTrue($container->has('server_grove_locale.flag_assetic_resource'));
        $this->assertTrue($container->has('server_grove_locale.locale_listener'));
        $this->assertTrue($container->has('server_grove_locale.flag_extension'));

        $this->assertTrue($container->hasParameter('server_grove_locale.hide_current_locale'));
        $this->assertTrue($container->hasParameter('server_grove_locale.enabled_locales'));
        $this->assertTrue($container->hasParameter('server_grove_locale.template'));
        $this->assertTrue($container->hasParameter('server_grove_locale.flags_path'));
    }

    /**
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testDomainException()
    {
        $container = new ContainerBuilder();

        $extension = new ServerGroveLocaleExtension();
        $extension->load(
            array(
                'server_grove_locale' => array(
                    'domains' => array(
                        0 => array('locale' => 'en', 'domain' => 'servergrove.com'),
                        1 => array('locale' => 'es', 'domain' => 'servergrove.es')
                    )
                )
            ), $container
        );
    }
}
