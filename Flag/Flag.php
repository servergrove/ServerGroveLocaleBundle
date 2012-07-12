<?php

namespace ServerGrove\LocaleBundle\Flag;

/**
 * Class Flag
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class Flag implements \Serializable
{
    private $file;

    private $locale;

    private $country;

    /**
     * @param string      $file
     * @param string      $locale
     * @param string|null $country
     */
    public function __construct($file, $locale, $country = null)
    {
        $this->file    = $file;
        $this->locale  = $locale;
        $this->country = $country;
    }

    /**
     * @return null|string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @return string
     */
    public function getLocaleString()
    {
        return $this->locale.(is_null($this->country) ? '' : '-'.$this->country);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getLocaleString();
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * String representation of object
     *
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        return serialize(array(
            'file'    => $this->file,
            'locale'  => $this->locale,
            'country' => $this->country
        ));
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Constructs the object
     *
     * @link http://php.net/manual/en/serializable.unserialize.php
     *
     * @param string $serialized <p>
     *                           The string representation of the object.
     * </p>
     *
     * @return mixed the original value unserialized.
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);

        $this->file    = $data['file'];
        $this->locale  = $data['locale'];
        $this->country = $data['country'];
    }

}
