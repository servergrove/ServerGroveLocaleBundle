<?php

namespace ServerGrove\LocaleBundle\Asset;

use Assetic\Asset\AssetInterface;
use Assetic\Filter\FilterInterface;

/**
 * Class LocaleAsset
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class LocaleAsset implements AssetInterface
{
    /** @var \Assetic\Asset\AssetInterface */
    private $asset;

    /** @var string */
    private $locale;

    /** @var null|string */
    private $country;

    /**
     * @param AssetInterface $asset
     * @param string         $locale
     * @param string         $country
     */
    public function __construct(AssetInterface $asset, $locale, $country = null)
    {
        $this->asset   = $asset;
        $this->locale  = $locale;
        $this->country = $country;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @return null|string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Ensures the current asset includes the supplied filter.
     *
     * @param FilterInterface $filter A filter
     */
    public function ensureFilter(FilterInterface $filter)
    {
        $this->asset->ensureFilter($filter);
    }

    /**
     * Returns an array of filters currently applied.
     *
     * @return array An array of filters
     */
    public function getFilters()
    {
        return $this->asset->getFilters();
    }

    /**
     * Clears all filters from the current asset.
     */
    public function clearFilters()
    {
        $this->asset->clearFilters();
    }

    /**
     * Loads the asset into memory and applies load filters.
     *
     * You may provide an additional filter to apply during load.
     *
     * @param FilterInterface $additionalFilter An additional filter
     */
    public function load(FilterInterface $additionalFilter = null)
    {
        $this->asset->load($additionalFilter);
    }

    /**
     * Applies dump filters and returns the asset as a string.
     *
     * You may provide an additional filter to apply during dump.
     *
     * Dumping an asset should not change its state.
     *
     * If the current asset has not been loaded yet, it should be
     * automatically loaded at this time.
     *
     * @param FilterInterface $additionalFilter An additional filter
     *
     * @return string The filtered content of the current asset
     */
    public function dump(FilterInterface $additionalFilter = null)
    {
        return $this->asset->dump($additionalFilter);
    }

    /**
     * Returns the loaded content of the current asset.
     *
     * @return string The content
     */
    public function getContent()
    {
        return $this->asset->getContent();
    }

    /**
     * Sets the content of the current asset.
     *
     * Filters can use this method to change the content of the asset.
     *
     * @param string $content The asset content
     */
    public function setContent($content)
    {
        $this->asset->setContent($content);
    }

    /**
     * Returns an absolute path or URL to the source asset's root directory.
     *
     * This value should be an absolute path to a directory in the filesystem,
     * an absolute URL with no path, or null.
     *
     * For example:
     *
     *  * '/path/to/web'
     *  * 'http://example.com'
     *  * null
     *
     * @return string|null The asset's root
     */
    public function getSourceRoot()
    {
        return $this->asset->getSourceRoot();
    }

    /**
     * Returns the relative path for the source asset.
     *
     * This value can be combined with the asset's source root (if both are
     * non-null) to get something compatible with file_get_contents().
     *
     * For example:
     *
     *  * 'js/main.js'
     *  * 'main.js'
     *  * null
     *
     * @return string|null The source asset path
     */
    public function getSourcePath()
    {
        return $this->asset->getSourcePath();
    }

    /**
     * Returns the URL for the current asset.
     *
     * @return string|null A web URL where the asset will be dumped
     */
    public function getTargetPath()
    {
        return $this->asset->getTargetPath();
    }

    /**
     * Sets the URL for the current asset.
     *
     * @param string $targetPath A web URL where the asset will be dumped
     */
    public function setTargetPath($targetPath)
    {
        $this->asset->setTargetPath($targetPath);
    }

    /**
     * Returns the time the current asset was last modified.
     *
     * @return integer|null A UNIX timestamp
     */
    public function getLastModified()
    {
        return $this->asset->getLastModified();
    }
}
