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

    /** @var array */
    private $defaults;

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
     * @return array
     */
    public function getDefaults()
    {
        $this->load();

        return $this->defaults;
    }

    /**
     * @param string $locale
     *
     * @return bool
     */
    public function forceDefault($locale)
    {
        $this->load();

        foreach ($this->flags as $flag) {
            /** @var $flag Flag */
            if (0 == strcasecmp($locale, $flag->getLocaleString())) {
                $this->defaults[$flag->getLocale()] = $flag->getLocaleString();

                return true;
            }
        }

        return false;
    }

    private function load()
    {
        if (!$this->loaded && is_readable($cache = $this->cacheDir.DIRECTORY_SEPARATOR.'flags.php')) {
            $cache = require $cache;

            $this->flags    = $cache['flags'];
            $this->defaults = $cache['defaults'];

            $this->loaded = true;
        }
    }
}
