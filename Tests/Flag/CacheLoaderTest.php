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
    /** @var string */
    private $cacheDir;

    /** @var \Symfony\Component\Filesystem\Filesystem */
    private $filesystem;

    public function testLoader()
    {
        $flags = array(
            'flags'   => array(
                'en-GB' => new \ServerGrove\LocaleBundle\Flag\Flag('/path/to/flags/en-GB.pnh', 'en', 'GB'),
                'es-ES' => new \ServerGrove\LocaleBundle\Flag\Flag('/path/to/flags/es-ES.pnh', 'es', 'ES')
            ),
            'defaults'=> array('es' => 'es-ES')
        );

        file_put_contents($this->cacheDir.DIRECTORY_SEPARATOR.'flags.php', sprintf('<?php return unserialize(%s);'.PHP_EOL, var_export(serialize($flags), true)));

        $loader = new CacheLoader($this->cacheDir);

        $this->assertAttributeEquals(false, 'loaded', $loader);
        $this->assertAttributeEquals(null, 'flags', $loader);
        $this->assertAttributeEquals(null, 'defaults', $loader);

        $cachedFlags    = $loader->getFlags();
        $cachedDefaults = $loader->getDefaults();

        $this->assertEquals($cachedFlags, $flags['flags']);
        $this->assertEquals($cachedDefaults, $flags['defaults']);
        $this->assertAttributeEquals(true, 'loaded', $loader);
        $this->assertAttributeEquals($flags['flags'], 'flags', $loader);
        $this->assertAttributeEquals($flags['defaults'], 'defaults', $loader);
    }

    protected function setUp()
    {
        $this->cacheDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid();

        $this->filesystem = new Filesystem();
        $this->filesystem->mkdir($this->cacheDir);
    }

    protected function tearDown()
    {
        $this->filesystem->remove($this->cacheDir);
        $this->filesystem = null;
    }
}
