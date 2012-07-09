<?php

namespace ServerGrove\LocaleBundle\Flag;

/**
 * Class CacheLoader
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class CacheLoader implements LoaderInterface
{

    /** @var string */
    private $cacheDir;

    /** @var bool */
    private $loaded;

    /** @var array */
    private $flags;

    /**
     * @param string $cacheDir
     */
    public function __construct($cacheDir)
    {
        $this->cacheDir = $cacheDir;
        $this->loaded   = false;
    }

    /**
     * @return array
     */
    public function getFlags()
    {
        $this->load();

        return $this->flags;
    }

    /**
     * @param string $locale
     */
    public function forceDefault($locale)
    {
        $this->load();

        if (isset($this->flags[$locale])) {
            $this->flags['defaults'][$this->flags[$locale]['locale']] = $this->flags[$locale];

            return true;
        }

        return false;
    }

    private function load()
    {
        if (!$this->loaded && is_readable($cache = $this->cacheDir.DIRECTORY_SEPARATOR.'flags.php')) {
            $this->flags = require $cache;

            $this->loaded = true;
        }
    }
}
