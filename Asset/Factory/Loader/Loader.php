<?php

namespace ServerGrove\LocaleBundle\Asset\Factory\Loader;

use Assetic\Factory\Loader\FormulaLoaderInterface;
use Assetic\Factory\Resource\ResourceInterface;
use Assetic\Factory\Resource\FileResource;
use ServerGrove\LocaleBundle\Asset\Factory\Resource\LocaleCacheResource;

/**
 * Class
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class Loader implements FormulaLoaderInterface
{

    /**
     * Loads formulae from a resource.
     *
     * Formulae should be loaded the same regardless of the current debug
     * mode. Debug considerations should happen downstream.
     *
     * @param ResourceInterface $resource A resource
     *
     * @return array An array of formulae
     */
    public function load(ResourceInterface $resource)
    {
        if (!($resource instanceof LocaleCacheResource)) {
            return array();
        }

        $images = array();

        /** @var $flagResource \ServerGrove\LocaleBundle\Asset\Factory\Resource\FlagResource */
        foreach ($resource as $flagResource) {
            $images[$flagResource->__toString()] = $flagResource->getInfo();
        }

        return $images;
    }

    private function getFormulae(FileResource $resource)
    {

    }
}
