<?php

namespace ServerGrove\LocaleBundle\Asset\Factory\Resource;

use Assetic\Factory\Resource\ResourceInterface;
use ServerGrove\LocaleBundle\Flag\Flag;

/**
 * Class FlagResource
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class FlagResource implements ResourceInterface
{
    private $flag;

    private $path;

    /**
     * @param \ServerGrove\LocaleBundle\Flag\Flag $flag
     * @param string                              $path
     */
    public function __construct(Flag $flag, $path)
    {
        $this->flag = $flag;
        $this->path = $path;
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
        return is_readable($this->flag->getFile()) && filemtime($this->flag->getFile()) <= $timestamp;
    }

    /**
     * Returns the content of the resource.
     *
     * @return string The content
     */
    public function getContent()
    {
        return file_get_contents($this->path.DIRECTORY_SEPARATOR.$this->flag->getFile());
    }

    /**
     * Returns a unique string for the current resource.
     *
     * @return string A unique string to identity the current resource
     */
    public function __toString()
    {
        return sprintf('locale_%s', preg_replace('/[^\w]+/', '_', $this->flag->getLocaleString()));
    }

    public function getInfo()
    {
        return array(
            array($this->flag->getFile()),
            array(),
            array(
                'output' => 'images/locale/'.basename($this->flag->getFile()),
                'name'   => $this->flag->getLocaleString()
            )
        );
    }
}
