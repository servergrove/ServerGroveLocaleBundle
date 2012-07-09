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
     * @param string $locale
     */
    public function forceDefault($locale);
}
