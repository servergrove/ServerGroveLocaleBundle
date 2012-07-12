<?php

namespace ServerGrove\LocaleBundle\Flag;

/**
 * Interface LoaderInterface
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
interface LoaderInterface
{
    /**
     * Returns an associative array of flags
     *
     * @abstract
     * @return array
     */
    public function getFlags();

    /**
     * Returns an associative array of default flags
     *
     * @abstract
     * @return array
     */
    public function getDefaults();

    /**
     * @param string $locale
     */
    public function forceDefault($locale);
}
