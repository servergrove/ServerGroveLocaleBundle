<?php

namespace ServerGrove\LocaleBundle\Tests\Flag;

use ServerGrove\LocaleBundle\Flag\CacheLoader;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class CacheLoaderTest
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class CacheLoaderTest extends \PHPUnit_Framework_TestCase
{
    /** @var array */
    private $flags;

    /** @var string */
    private $cacheDir;

    /** @var \Symfony\Component\Filesystem\Filesystem */
    private $filesystem;

    public function testLoader()
    {
        $loader = new CacheLoader($this->cacheDir);

        $this->assertAttributeEquals(false, 'loaded', $loader);
        $this->assertAttributeEquals(null, 'flags', $loader);
        $this->assertAttributeEquals(null, 'defaults', $loader);

        $cachedFlags    = $loader->getFlags();
        $cachedDefaults = $loader->getDefaults();

        $this->assertEquals($cachedFlags, $this->flags['flags']);
        $this->assertEquals($cachedDefaults, $this->flags['defaults']);
        $this->assertAttributeEquals(true, 'loaded', $loader);
        $this->assertAttributeEquals($this->flags['flags'], 'flags', $loader);
        $this->assertAttributeEquals($this->flags['defaults'], 'defaults', $loader);
    }

    public function testForceDefault()
    {
        $loader = new CacheLoader($this->cacheDir);

        $defaults = $loader->getDefaults();
        $this->assertEquals('en-US', $defaults['en']);

        $this->assertTrue($loader->forceDefault('en-GB'));

        $defaults = $loader->getDefaults();
        $this->assertEquals('en-GB', $defaults['en']);

        $this->assertFalse($loader->forceDefault('en-AU'));
        $this->assertEquals('en-GB', $defaults['en']);
    }

    protected function setUp()
    {
        $this->cacheDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid();

        $this->filesystem = new Filesystem();
        $this->filesystem->mkdir($this->cacheDir);

        $this->flags = array(
            'flags'   => array(
                'en-GB' => new \ServerGrove\LocaleBundle\Flag\Flag('/path/to/flags/en-GB.pnh', 'en', 'GB'),
                'es-ES' => new \ServerGrove\LocaleBundle\Flag\Flag('/path/to/flags/es-ES.pnh', 'es', 'ES')
            ),
            'defaults'=> array('es' => 'es-ES', 'en' => 'en-US')
        );

        file_put_contents($this->cacheDir.DIRECTORY_SEPARATOR.'flags.php', sprintf('<?php return unserialize(%s);'.PHP_EOL, var_export(serialize($this->flags), true)));

    }

    protected function tearDown()
    {
        $this->filesystem->remove($this->cacheDir);
        $this->filesystem = null;
    }
}
