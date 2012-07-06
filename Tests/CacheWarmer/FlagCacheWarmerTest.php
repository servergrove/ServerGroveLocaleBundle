<?php

namespace ServerGrove\LocaleBundle\Tests\CacheWarmer;

use ServerGrove\LocaleBundle\CacheWarmer\FlagCacheWarmer;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class FlagCacheWarmerTest
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class FlagCacheWarmerTest extends \PHPUnit_Framework_TestCase
{

    private $cacheDir;

    /**
     * @dataProvider getTestData
     */
    public function testCacheWarmer($bundleName, $flagsPath, array $patterns, array $settings)
    {
        $map = $this->getWarmerProcessedMap($bundleName, $flagsPath, $patterns, $settings);

        $this->assertArrayHasKey('defaults', $map, 'The default flags were not found');

        $this->assertArrayHasKey('es', $map['defaults']);
        $this->assertArrayHasKey('en', $map['defaults']);
        $this->assertArrayHasKey('pt', $map['defaults']);

        foreach ($settings['files'] as $locale => $info) {
            if (isset($info['countries'])) {
                foreach ($info['countries'] as $country) {
                    $this->assertArrayHasKey($locale.'-'.$country, $map);
                }
            }
        }
    }

    /**
     * @dataProvider getTestData
     */
    public function testEnabledLocales($bundleName, $flagsPath, array $patterns, array $settings)
    {
        $map = $this->getWarmerProcessedMap($bundleName, $flagsPath, $patterns, array_merge($settings, array('enabled_locales' => array('en'))));

        $this->assertArrayHasKey('en', $map['defaults']);
        $this->assertArrayNotHasKey('es', $map['defaults']);
        $this->assertArrayNotHasKey('pt', $map['defaults']);

        $map = $this->getWarmerProcessedMap($bundleName, $flagsPath, $patterns, array_merge($settings, array('enabled_locales' => array(
            'en',
            'es'
        ))));

        $this->assertArrayHasKey('en', $map['defaults']);
        $this->assertArrayHasKey('es', $map['defaults']);
        $this->assertArrayNotHasKey('pt', $map['defaults']);

        $map = $this->getWarmerProcessedMap($bundleName, $flagsPath, $patterns, array_merge($settings, array('enabled_locales' => array(
            'en',
            'es*'
        ))));

        $this->assertArrayNotHasKey('en-UK', $map);
        $this->assertArrayNotHasKey('en-US', $map);
        $this->assertArrayNotHasKey('pt', $map);
        $this->assertArrayNotHasKey('pt-BR', $map);
        $this->assertArrayNotHasKey('pt-PT', $map);

        if (isset($settings['file']['es'], $settings['file']['es']['countries'])) {
            foreach ($settings['file']['es']['countries'] as $country) {
                $this->assertArrayHasKey('es-'.$country, $map);
            }
        }
    }

    public function getTestData()
    {
        return array(
            array(
                'ServerGroveLocaleBundle1',
                ':cacheDir/src/ServerGroveLocaleBundle1/Resources/public/images',
                array('/^(?P<locale>[a-z]{2})\.png$/'),
                array(
                    'files' => array(
                        'es' => true,
                        'en' => true,
                        'pt' => true
                    )
                )
            ),
            array(
                'ServerGroveLocaleBundle1',
                'images/',
                array('/^(?P<locale>[a-z]{2})\.png$/'),
                array(
                    'files'      => array(
                        'es' => true,
                        'en' => true,
                        'pt' => true
                    ),
                    'flags_path' => ':cacheDir/web/images',
                )
            ),
            array(
                'ServerGroveLocaleBundle2',
                ':cacheDir/src/ServerGroveLocaleBundle2/Resources/public/images',
                array('/^(?P<locale>[a-z]{2})\-(?P<country>[A-Z]{2})\.png$/'),
                array(
                    'files' => array(
                        'es' => array('countries'=> array('ES', 'AR', 'MX')),
                        'en' => array('countries'=> array('US', 'UK', 'AU')),
                        'pt' => array('countries'=> array('BR', 'PT'))
                    )
                )
            )
        );
    }

    protected function setUp()
    {
        parent::setUp();

        $this->cacheDir = sys_get_temp_dir().'/'.uniqid();
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }

        $fs = new Filesystem();
        $fs->mkdir(array(
            $this->cacheDir.'/app/cache/test',
            $this->cacheDir.'/app/logs',
            $this->cacheDir.'/src',
            $this->cacheDir.'/web'
        ));

        $bundle   = new \ServerGrove\LocaleBundle\ServerGroveLocaleBundle();
        $testData = $this->getTestData();

        foreach ($testData as $config) {
            list(, $path, , $settings) = $config;

            if (isset($settings['flags_path'])) {
                $path = $settings['flags_path'];
            }

            $destinationPath = strtr($path, array(':cacheDir' => $this->cacheDir));
            foreach ($settings['files'] as $lang => $setting) {
                $langFile = $bundle->getPath().'/Resources/public/images/'.$lang.'.png';
                if (is_array($setting)) {
                    if (isset($setting['countries'])) {
                        foreach ($setting['countries'] as $country) {
                            $fs->copy($langFile, $destinationPath.'/'.$lang.'-'.$country.'.png');
                        }
                    } else {
                        $fs->copy($langFile, $destinationPath.'/'.$setting['name'].'.png');
                    }
                } else {
                    $fs->copy($langFile, $destinationPath.'/'.$lang.'.png');
                }
            }
        }
    }

    protected function tearDown()
    {
        $fs = new Filesystem();
        $fs->remove($this->cacheDir);

        parent::tearDown();
    }

    /**
     * @param string $bundleName
     *
     * @return \Symfony\Component\HttpKernel\Kernel
     */
    private function getKernelForBundleTest($bundleName)
    {
        $bundles = array(
            'ServerGroveLocaleBundle' => new \ServerGrove\LocaleBundle\ServerGroveLocaleBundle(),
            $bundleName               => $this->getMockForBundle($bundleName),
        );

        $mock = $this->getMock('Symfony\Component\HttpKernel\KernelInterface');

        $mock
            ->expects($this->any())
            ->method('getBundle')
            ->will($this->returnCallback(
            function($name) use ($bundles) {
                return $bundles[$name];
            }));

        $mock
            ->expects($this->any())
            ->method('getRootDir')
            ->will($this->returnValue($this->cacheDir.'/app'));

        $mock
            ->expects($this->any())
            ->method('getCacheDir')
            ->will($this->returnValue($this->cacheDir.'/app/cache/test'));

        $mock
            ->expects($this->any())
            ->method('locateResource')
            ->will($this->returnValue($this->cacheDir.'/src/'.$bundleName.'/Resources/public/images'));

        return $mock;
    }

    /**
     * @param $name
     *
     * @return \Symfony\Component\HttpKernel\Bundle\BundleInterface
     */
    private function getMockForBundle($name)
    {
        $mock = $this->getMock('Symfony\Component\HttpKernel\Bundle\BundleInterface');
        $mock
            ->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue($this->cacheDir.'/src/'.$name));

        return $mock;
    }

    private function getWarmerProcessedMap($bundleName, $flagsPath, $patterns, $settings)
    {
        $kernel = $this->getKernelForBundleTest($bundleName);

        $warmer = new FlagCacheWarmer(
            $kernel->getRootDir(),
            strtr($flagsPath, array(':cacheDir' => $this->cacheDir)),
            $patterns,
            isset($settings['defaults']) ? $settings['defaults'] : array(),
            isset($settings['enabled_locales']) ? $settings['enabled_locales'] : array()
        );

        $this->assertFalse($warmer->isOptional(), 'Warmer should not be optional');

        $warmer->warmUp($kernel->getCacheDir());

        $this->assertFileExists($file = $kernel->getCacheDir().'/flags.php');

        return require $file;
    }
}
