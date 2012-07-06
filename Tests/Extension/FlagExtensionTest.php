<?php

namespace ServerGrove\LocaleBundle\Tests\Extension;

use ServerGrove\LocaleBundle\Asset\Factory\AssetFactory;
use ServerGrove\LocaleBundle\Extension\FlagExtension;

/**
 * Class FlagExtensionTest
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class FlagExtensionTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Twig_Environment */
    private $twig;

    /** @var string */
    private $template;

    public function testExtension()
    {
        $extension = $this->createExtension(array());

        $this->assertEquals('flag', $extension->getName());

        $functions = $extension->getFunctions();
        $this->assertArrayHasKey('flag', $functions);
    }

    public function testFlagAttributes()
    {
        $extension = $this->createExtension(array(
            'en' => array(
                'file'    => 'en.png',
                'locale'  => 'en',
                'country' => null
            )
        ));

        $this->assertEquals('<img src="/images/locale/flag-en.png"/>', $extension->renderFlag('en'));
        $this->assertEquals('<img src="/images/locale/flag-en.png" alt="Testing"/>', $extension->renderFlag('en', null, array('attrs' => array('alt' => 'Testing'))));
        $this->assertEquals('<img src="/images/locale/flag-en.png" alt="Testing"/>', $extension->renderFlag('en', null, array('attrs' => array('alt' => array('en' => 'Testing')))));
        $this->assertEquals('<img src="/images/locale/flag-en.png"/>', $extension->renderFlag('en', null, array('attrs' => array('alt' => array('es' => 'Testing')))));
    }

    /**
     * @dataProvider getFlagData
     */
    public function testRenderFlag($locale, $country, $image, array $attrs, array $mapping)
    {
        $extension = $this->createExtension($mapping);

        $content = $extension->renderFlag($locale, $country, array('attrs' => $attrs));

        $this->assertNotEmpty($content, 'Flag rendering is empty');
        $this->assertTag(array(
            'tag'        => 'img',
            'attributes' => array_merge($attrs, array('src' => $image))
        ), $content, sprintf('Invalid image tag%s%1$s- Actual: %s%1$s+ Expected: %s%1$s', PHP_EOL, $content, $image));
    }

    /**
     * @dataProvider getFlagData
     */
    public function testRenderUrlFlag($locale, $country, $image, array $attrs, array $mapping)
    {
        $extension = $this->createExtension($mapping, $domains = array('default' => 'http://servergrove.com'));

        $content = $extension->renderDomainFlag($locale, $country, array('attrs' => $attrs));

        $this->assertNotEmpty($content, 'Flag rendering is empty');
        $this->assertTag(array(
            'tag'        => 'a',
            'attributes' => array('href' => $domains[isset($domains[$locale]) ? $locale : 'default']),
            'child'      => array(
                'tag' => 'img'
            )
        ), $content, sprintf('Invalid image tag%s%1$s- Actual: %s%1$s+ Expected: %s%1$s', PHP_EOL, $content, $image));
    }

    /**
     * @dataProvider getFlagsData
     */
    public function testRenderFlags(array $mapping)
    {
        $extension = $this->createExtension($mapping);

        $content = $extension->renderFlags();
        $this->assertNotEmpty($content, 'Flags rendering is empty');

        $images = '';
        foreach ($mapping as $locale => $info) {
            if ('defaults' != $locale) {
                $images .= sprintf('<img src="/images/locale/flag-%s"/>', $info['file']).PHP_EOL;
            }
        }
    }

    public function testGetAssetUrl()
    {
        $extension = $this->createExtension(array(), $domains = array(
            'default' => 'http://servergrove.com',
            'en'      => 'http://servergrove.com',
            'en-UK'   => 'http://servergrove.eu',
            'es'      => 'http://servergrove.es',
            'es-AR'   => 'http://servergrove.com.ar',
            'pt'      => 'http://servergrove.com.br'
        ));

        $test   = $this;
        $assert = function($expected, $actual) use ($domains, $extension, $test) {
            $test->assertEquals($domains[$expected], $extension->getAssetUrl($actual), sprintf('Incorrect behavior when looking url for "%s"', $actual));
        };

        $assert('en', 'en');
        $assert('en-UK', 'en-UK');
        $assert('en', 'en-AU');
        $assert('es', 'es');
        $assert('es-AR', 'es-AR');
        $assert('es', 'es-UY');
        $assert('pt', 'pt');
        $assert('pt', 'pt-BR');
        $assert('default', 'fr');
        $assert('default', 'de');
    }

    /**
     * @dataProvider      getExceptionData
     * @expectedException InvalidArgumentException
     */
    public function testFlagsExceptions($locale, $country, $mapping)
    {
        $extension = $this->createExtension($mapping);
        $extension->renderFlag($locale, $country);
    }

    /**
     * @return array
     */
    public function getFlagData()
    {

        $simple = array('defaults' => $this->getMap('en'));

        $es = array_merge(array('defaults' => $this->getMap('es', 'ES', 'es')),
            $this->getMap('es', 'ES'),
            $this->getMap('es', 'AR')
        );

        $en = array_merge(array('defaults' => $this->getMap('en', 'US', 'en')),
            $this->getMap('en', 'UK'),
            $this->getMap('en', 'US')
        );

        $pt = array_merge(array('defaults' => $this->getMap('pt', 'PT', 'pt')),
            $this->getMap('pt', 'PT'),
            $this->getMap('pt', 'BR')
        );

        return array(
            array('es', null, '/images/locale/flag-es-ES.png', array('alt' => 'Spanish'), $es),
            array('es', 'AR', '/images/locale/flag-es-AR.png', array(), $es),

            array('en', null, '/images/locale/flag-en.png', array('title' => 'English'), $simple),
            array('en', null, '/images/locale/flag-en-US.png', array(), $en),
            array('en', 'UK', '/images/locale/flag-en-UK.png', array(), $en),

            array('pt', null, '/images/locale/flag-pt-PT.png', array(), $pt),
            array('pt', 'BR', '/images/locale/flag-pt-BR.png', array(), $pt)
        );
    }

    /**
     * @return array
     */
    public function getFlagsData()
    {
        return array(
            array(
                array_merge(
                    array('defaults'=> $this->getMap('es', 'ES', 'es')),
                    $this->getMap('es', 'ES'),
                    $this->getMap('es', 'AR')
                )
            ),

            array(
                array_merge(
                    array('defaults'=> $this->getMap('en', 'US', 'en')),
                    $this->getMap('en', 'UK'),
                    $this->getMap('en', 'US')
                )
            ),

            array(
                array_merge(
                    array('defaults'=> $this->getMap('pt', 'BR', 'pt')),
                    $this->getMap('pt', 'PT'),
                    $this->getMap('pt', 'BR')
                )
            ),
        );
    }

    /**
     * @return array
     */
    public function getExceptionData()
    {
        return array(
            array('fr', null, array_merge(
                array('defaults'=> $this->getMap('en', 'US', 'en')),
                $this->getMap('en', 'UK'),
                $this->getMap('en', 'US')
            )),

            array('de', null, array_merge(
                array('defaults'=> $this->getMap('es', 'ES', 'es')),
                $this->getMap('es', 'ES'),
                $this->getMap('es', 'AR')
            )),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->twig = new \Twig_Environment(new \Twig_Loader_Filesystem(dirname(dirname(__DIR__)).'/Resources/views'));

        $generator = $this->getMock('Symfony\Component\Routing\Generator\UrlGeneratorInterface');
        $generator
            ->expects($this->any())
            ->method('generate')
            ->will($this->returnValue('/'));

        $routingExtension = new \Symfony\Bridge\Twig\Extension\RoutingExtension($generator);

        $this->twig->addExtension($routingExtension);
        $routingExtension->initRuntime($this->twig);

        $this->template = 'flags.html.twig';
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        $this->twig = null;

        parent::tearDown();
    }

    /**
     * @param array $flags
     *
     * @return \ServerGrove\LocaleBundle\Extension\FlagExtension
     */
    private function createExtension(array $flags, array $domains = array())
    {
        /** @var $loader \ServerGrove\LocaleBundle\Flag\LoaderInterface */
        $loader = $this
            ->getMockBuilder('ServerGrove\LocaleBundle\Flag\LoaderInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $loader->expects($this->any())->method('getFlags')->will($this->returnValue($flags));

        $factory = new AssetFactory($loader, dirname(dirname(__DIR__)).'/Resources/public/images', true);
        $factory->load();

        $extension = new FlagExtension($factory, $this->template, $domains);
        $this->twig->addExtension($extension);
        $extension->initRuntime($this->twig);

        return $extension;
    }

    private function getMap($locale, $country = null, $key = null)
    {
        $image = $locale.(is_null($country) ? '' : '-'.$country);
        $l     = is_null($key)
            ? $image
            : $key;

        return array($l => array(
            'file'    => $image.'.png',
            'locale'  => $locale,
            'country' => is_null($key) ? $country : null
        ));
    }
}
