<?php

namespace ServerGrove\LocaleBundle\Tests\Extension;

use ServerGrove\LocaleBundle\Tests\TestCase;

/**
 * Class FlagExtensionTest
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class FlagExtensionTest extends TestCase
{
    public function testHideLocale()
    {
        $extension = $this->createExtension();

        $this->assertAttributeEmpty('hiddenLocales', $extension);

        $extension->hideLocale('es');

        $this->assertAttributeNotEmpty('hiddenLocales', $extension);
    }

    public function testIsAssetVisible()
    {
        $extension = $this->createExtension();

        $this->assertTrue($extension->isAssetVisible('en'));
        $extension->hideLocale('en');
        $this->assertFalse($extension->isAssetVisible('en'));
    }

    public function testRenderAssetFlag()
    {
        $extension = $this->createExtension();

        $content = $extension->renderAssetFlag('locale_en');
        $this->assertEquals('<img src="/images/locale/en.png"/>', $content);

        $content = $extension->renderAssetFlag('locale_en_gb');
        $this->assertEquals('<img src="/images/locale/en-GB.png"/>', $content);
    }

    public function testRenderFlag()
    {
        $extension = $this->createExtension();

        $content = $extension->renderFlag('en');
        $this->assertEquals('<img src="/images/locale/en.png"/>', $content);

        $content = $extension->renderFlag('en', 'GB');
        $this->assertEquals('<img src="/images/locale/en-GB.png"/>', $content);
    }

    /**
     * @dataProvider defaultsProvider
     */
    public function testRenderFlags($defaults)
    {
        $extension = $this->createExtension(array('defaults' => $defaults));

        $content = $extension->renderFlags();
        $this->assertNotEmpty($content);
        $this->assertEquals($this->getImagesHtmlForLocales($defaults), $content);
    }

    public function defaultsProvider()
    {
        return array(
            array(array('en' => 'en', 'es' => 'es', 'pt' => 'pt')),
            array(array('en' => 'en-GB', 'es' => 'es-ES', 'pt' => 'pt-BR'))
        );
    }

    public function testRenderPathAssetFlag()
    {
        $extension = $this->createExtension();

        $content = $extension->renderPathAssetFlag('my_route', 'locale_en');
        $this->assertEquals('<a href="/"><img src="/images/locale/en.png"/></a>', $content);

        $content = $extension->renderPathAssetFlag('my_route', 'locale_en_gb');
        $this->assertEquals('<a href="/"><img src="/images/locale/en-GB.png"/></a>', $content);
    }

    public function testRenderPathFlag()
    {
        $extension = $this->createExtension();

        $content = $extension->renderPathFlag('my_route', 'en');
        $this->assertEquals('<a href="/"><img src="/images/locale/en.png"/></a>', $content);

        $content = $extension->renderPathFlag('my_route', 'en', array(), 'GB');
        $this->assertEquals('<a href="/"><img src="/images/locale/en-GB.png"/></a>', $content);
    }

    /**
     * @dataProvider defaultsProvider
     */
    public function testRenderPathFlags(array $defaults)
    {
        $extension = $this->createExtension(array('defaults' => $defaults));

        $content = $extension->renderPathFlags('my_route');
        $this->assertNotEmpty($content);
        $this->assertEquals($this->getLinkedImagesHtmlForLocales($defaults), $content);
    }

    public function testRenderUrlAssetFlag()
    {
        $extension = $this->createExtension();

        $content = $extension->renderUrlAssetFlag('http://servergrove.com', 'locale_en');
        $this->assertEquals('<a href="http://servergrove.com"><img src="/images/locale/en.png"/></a>', $content);

        $content = $extension->renderUrlAssetFlag('http://servergrove.eu', 'locale_en_gb');
        $this->assertEquals('<a href="http://servergrove.eu"><img src="/images/locale/en-GB.png"/></a>', $content);
    }

    public function testRenderUrlFlag()
    {
        $extension = $this->createExtension();

        $content = $extension->renderUrlFlag('http://servergrove.com', 'en');
        $this->assertEquals('<a href="http://servergrove.com"><img src="/images/locale/en.png"/></a>', $content);

        $content = $extension->renderUrlFlag('http://servergrove.eu', 'en', 'GB');
        $this->assertEquals('<a href="http://servergrove.eu"><img src="/images/locale/en-GB.png"/></a>', $content);
    }

    public function testRenderDomainFlag()
    {
        $extension = $this->createExtension(array('domains' => $this->getTestDomains()));

        $content = $extension->renderDomainFlag('en');
        $this->assertEquals('<a href="http://servergrove.com"><img src="/images/locale/en.png"/></a>', $content);

        $content = $extension->renderDomainFlag('en', 'GB');
        $this->assertEquals('<a href="http://servergrove.eu"><img src="/images/locale/en-GB.png"/></a>', $content);
    }

    /**
     * @dataProvider defaultsProvider
     */
    public function testRenderDomainsFlags(array $defaults)
    {
        $extension = $this->createExtension(array('defaults' => $defaults, 'domains' => $this->getTestDomains()));

        $content = $extension->renderDomainsFlags();
        $this->assertNotEmpty($content);
        $this->assertEquals($this->getUrlImagesForLocales($defaults), $content);
    }

    public function testRenderLinkedFlag()
    {
        $extension = $this->createExtension();

        $content = $extension->renderLinkedFlag('http://servergrove.com', 'locale_en');
        $this->assertEquals('<a href="http://servergrove.com"><img src="/images/locale/en.png"/></a>', $content);
    }

    public function testGetAssetUrl()
    {
        $extension = $this->createExtension(array('domains' => $this->getTestDomains()));

        $url = $extension->getAssetUrl('locale_en');
        $this->assertEquals('http://servergrove.com', $url);

        $url = $extension->getAssetUrl('locale_en_gb');
        $this->assertEquals('http://servergrove.eu', $url);

        $url = $extension->getAssetUrl('locale_fr');
        $this->assertEquals('http://servergrove.com', $url);
    }

    private function getImagesHtmlForLocales(array $defaults)
    {
        $expected = '';
        foreach ($defaults as $locale) {
            $expected .= sprintf('<img src="/images/locale/%s.png"/>', $locale).PHP_EOL;
        }

        return $expected;
    }

    private function getLinkedImagesHtmlForLocales(array $defaults)
    {
        $expected = '';
        foreach ($defaults as $locale) {
            $expected .= sprintf('<a href="/"><img src="/images/locale/%s.png"/></a>', $locale).PHP_EOL;
        }

        return $expected;
    }

    private function getUrlImagesForLocales($defaults)
    {
        $domains = $this->getTestDomains();

        $expected = '';
        foreach ($defaults as $locale) {
            if (isset($domains[$lowerLocale = strtolower($locale)])) {
                $url = $domains[$lowerLocale];
            } elseif (preg_match('/^(?P<locale>[a-z]{2})\-[A-Z]{2}$/i', $locale, $out) && isset($domains[$out['locale']])) {
                $url = $domains[$out['locale']];
            } else {
                $url = $domains['default'];
            }

            $expected .= sprintf('<a href="%s"><img src="/images/locale/%s.png"/></a>', $url, $locale).PHP_EOL;
        }

        return $expected;
    }

    /**
     * @return array
     */
    private function getTestDomains()
    {
        return array(
            'default' => 'http://servergrove.com',
            'en-gb'   => 'http://servergrove.eu',
            'es'      => 'http://servergrove.es',
            'es-ar'   => 'http://servergrove.com.ar',
            'pt-br'   => 'http://servergrove.com.br'
        );
    }
}
