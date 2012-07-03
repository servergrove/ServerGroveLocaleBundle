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
            'defaults'=> array('es' => 'file.png')
        );

        file_put_contents($this->cacheDir.DIRECTORY_SEPARATOR.'flags.php', sprintf('<?php return %s;'.PHP_EOL, var_export($flags, true)));

        $loader = new CacheLoader($this->cacheDir);

        $this->assertAttributeEquals(false, 'loaded', $loader);
        $this->assertAttributeEquals(null, 'flags', $loader);

        $cachedFlags = $loader->getFlags();

        $this->assertEquals($cachedFlags, $flags);
        $this->assertAttributeEquals(true, 'loaded', $loader);
        $this->assertAttributeEquals($flags, 'flags', $loader);
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
