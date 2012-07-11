<?php

namespace ServerGrove\LocaleBundle\Asset\Factory\Resource;

use Assetic\Factory\Resource\IteratorResourceInterface;
use ServerGrove\LocaleBundle\Flag\LoaderInterface;
use ServerGrove\LocaleBundle\Flag\Flag;

/**
 * Class LocaleCacheResource
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class LocaleCacheResource implements IteratorResourceInterface
{
    private $loader;

    private $path;

    /**
     * @param \ServerGrove\LocaleBundle\Flag\LoaderInterface $loader
     * @param string                                         $path
     */
    public function __construct(LoaderInterface $loader, $path)
    {
        $this->loader = $loader;
        $this->path   = $path;
    }

    /**
     * Checks if a timestamp represents the latest resource.
     *
     * @param integer $timestamp A UNIX timestamp
     *
     * @return Boolean True if the timestamp is up to date
     */
    public function isFresh($timestamp)
    {
        foreach ($this as $resource) {
            if (!$resource->isFresh($timestamp)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns the content of the resource.
     *
     * @return string The content
     */
    public function getContent()
    {
        foreach ($this as $resource) {
            //var_dump(__FILE__, $resource);
        }

        return array();
    }

    /**
     * Returns a unique string for the current resource.
     *
     * @return string A unique string to identity the current resource
     */
    public function __toString()
    {

    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     *
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator()
    {
        return new FlagIterator($this->loader->getFlags(), $this->path);
    }
}

/**
 * Class FlagIterator
 */
class FlagIterator extends \ArrayIterator
{
    private $path;

    /**
     * @param array  $array
     * @param string $path
     */
    public function __construct($array = array(), $path = '')
    {
        parent::__construct($array, 0);
        $this->path = $path;
    }

    /**
     * @return mixed|FlagResource
     */
    public function current()
    {
        return new FlagResource(parent::current(), $this->path);
    }
}
