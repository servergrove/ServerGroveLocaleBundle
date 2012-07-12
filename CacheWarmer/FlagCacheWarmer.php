<?php

namespace ServerGrove\LocaleBundle\CacheWarmer;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmer;
use ServerGrove\LocaleBundle\Flag\Flag;

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

        $cache = array('flags' => array(), 'defaults' => array());

        /** @var $file \Symfony\Component\Finder\SplFileInfo */
        foreach ($files as $file) {
            $counter = 0;
            do {
                $match = preg_match($this->patterns[$counter], $file->getBasename(), $out);
            } while (!$match && $counter++ < count($this->patterns));

            $locale       = $out['locale'];
            $country      = isset($out['country']) ? strtolower($out['country']) : null;
            $localeString = $locale.(is_null($country) ? '' : '-'.$country);

            if ($this->isLocaleEnabled($localeString)) {
                $cache['flags'][$localeString] = new Flag($file->getRealPath(), $locale, $country);

                if (!isset($cache['defaults'][$locale])) {
                    if (isset($this->defaults[$locale])) {
                        $cache['defaults'][$locale] = strtolower($this->defaults[$locale]);
                    } else {
                        $cache['defaults'][$locale] = $localeString;
                    }
                }
            }
        }

        $this->writeCacheFile($cacheDir.DIRECTORY_SEPARATOR.'flags.php', sprintf('<?php return unserialize(%s);'.PHP_EOL, var_export(serialize($cache), true)));
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

        foreach ($this->enabledLocales as $enabledLocale) {
            if (0 == strcasecmp($locale, $enabledLocale)) {
                return true;
            }

            if (strpos($enabledLocale, '*') !== false && preg_match('/'.str_replace('*', '.*', $enabledLocale).'/', $locale)) {
                return true;
            }
        }

        return false;
    }
}
