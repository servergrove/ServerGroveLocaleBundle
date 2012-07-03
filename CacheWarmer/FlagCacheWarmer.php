<?php

namespace ServerGrove\LocaleBundle\CacheWarmer;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmer;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class FlagCacheWarmer
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class FlagCacheWarmer extends CacheWarmer
{
    /** @var string */
    private $rootDir;

    /** @var string */
    private $flagsPath;

    /** @var array */
    private $patterns;

    /** @var array */
    private $defaults;

    /**
     * @param KernelInterface $kernel
     * @param string          $flagsPath
     * @param array           $patterns
     * @param array           $defaults
     */
    public function __construct($rootDir, $flagsPath, array $patterns, array $defaults = array())
    {
        $this->rootDir   = $rootDir;
        $this->flagsPath = $flagsPath;
        $this->patterns  = $patterns;
        $this->defaults  = $defaults;
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp($cacheDir)
    {
        $finder = new \Symfony\Component\Finder\Finder();

        $finder = $finder->files()->sortByName();
        foreach ($this->patterns as $pattern) {
            $finder->name($pattern);
        }

        $files = $finder->in($flagsPath = $this->getAbsolutePath($this->flagsPath));
        $map   = array();

        /** @var $file \Symfony\Component\Finder\SplFileInfo */
        foreach ($files as $file) {
            $counter = 0;
            do {
                $match = preg_match($this->patterns[$counter], $file->getBasename(), $out);
            } while (!$match && $counter++ < count($this->patterns));

            $locale   = $out['locale'];
            $resource = str_replace(realpath($flagsPath).'/', '', $file->getRealPath());
            if (!isset($this->defaults[$locale])) {
                $this->defaults[$locale] = array('file' => $resource, 'locale' => $locale);
            } elseif (is_string($this->defaults[$locale])) {
                $this->defaults[$locale] = array('file' => $this->defaults[$locale], 'locale' => $locale);
            }

            if (isset($out['country'])) {
                $map[$locale.'-'.$out['country']] = array(
                    'file'    => $resource,
                    'locale'  => $locale,
                    'country' => $out['country']
                );
            }
        }

        $map['defaults'] = $this->defaults;

        $this->writeCacheFile($cacheDir.DIRECTORY_SEPARATOR.'flags.php', sprintf('<?php return %s;'.PHP_EOL, var_export($map, true)));
    }

    /**
     * {@inheritdoc}
     */
    public function isOptional()
    {
        return false;
    }

    /**
     * @param $path
     *
     * @return mixed|string
     */
    private function getAbsolutePath($path)
    {
        if ('/' != substr($path, 0, 1)) {
            $path = dirname($this->rootDir).'/web';
        }

        return $path;
    }
}
