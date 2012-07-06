<?php

namespace ServerGrove\LocaleBundle\CacheWarmer;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmer;

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

    /** @var array */
    private $enabledLocales;

    /**
     * Constructor
     *
     * @param string $rootDir
     * @param string $flagsPath
     * @param array  $patterns
     * @param array  $defaults
     * @param array  $enabledLocales
     */
    public function __construct($rootDir, $flagsPath, array $patterns, array $defaults = array(), array $enabledLocales = array())
    {
        $this->rootDir        = $rootDir;
        $this->flagsPath      = $flagsPath;
        $this->patterns       = $patterns;
        $this->defaults       = $defaults;
        $this->enabledLocales = $enabledLocales;
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp($cacheDir)
    {
        $finder = new \Symfony\Component\Finder\Finder();

        $finder = $finder->files()->sort(function ($a, $b) {
            $result = strcmp($a->getRealpath(), $b->getRealpath());

            return 1 == $result && 0 == substr_compare($a->getBasename(), $b->getBasename(), 0, 2) ? -1 : $result;
        });
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

            $locale = $out['locale'];
            if ($this->isLocaleEnabled($locale)) {
                $resource = str_replace(realpath($flagsPath).'/', '', $file->getRealPath());
                if (!isset($this->defaults[$locale])) {
                    $this->defaults[$locale] = array('file' => $resource, 'locale' => $locale);
                } elseif (is_string($this->defaults[$locale])) {
                    $this->defaults[$locale] = array('file' => $this->defaults[$locale], 'locale' => $locale);
                }
            }

            if (isset($out['country']) && $this->isLocaleEnabled($localeString = $locale.'-'.$out['country'])) {
                $map[$localeString] = array(
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

    private function isLocaleEnabled($locale)
    {
        if (0 == count($this->enabledLocales)) {
            return true;
        }

        if (in_array($locale, $this->enabledLocales)) {
            return true;
        }

        foreach ($this->enabledLocales as $enabledLocale) {
            if (strpos($enabledLocale, '*') !== false && preg_match('/'.str_replace('*', '.*', $enabledLocale).'/', $locale)) {
                return true;
            }
        }

        return false;
    }
}
