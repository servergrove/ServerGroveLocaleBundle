<?php

namespace ServerGrove\LocaleBundle\Extension;

use ServerGrove\LocaleBundle\Asset\Factory\AssetFactory;

/**
 * Class FlagExtension
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class FlagExtension extends \Twig_Extension
{
    /** @var \ServerGrove\LocaleBundle\Asset\Factory\AssetFactory */
    private $factory;

    /** @var \Twig_Environment */
    private $environment;

    /** @var string|\Twig_Template */
    private $template;

    /** @var array */
    private $hiddenLocales;

    /** @var array */
    private $domains;

    /**
     * Constructor
     *
     * @param AssetFactory          $factory
     * @param string|\Twig_Template $template
     * @param array                 $domains
     */
    public function __construct(AssetFactory $factory, $template, array $domains)
    {
        $this->factory       = $factory;
        $this->template      = $template;
        $this->hiddenLocales = array();
        $this->domains       = $domains;
    }

    /**
     * {@inheritdoc}
     */
    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * @param string $locale
     */
    public function hideLocale($locale)
    {
        $this->hiddenLocales[] = $locale;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        $options = array('is_safe' => array('html'));

        return array(
            'asset_flag'      => new \Twig_Function_Method($this, 'renderAssetFlag', $options),
            'flag'            => new \Twig_Function_Method($this, 'renderFlag', $options),
            'flags'           => new \Twig_Function_Method($this, 'renderFlags', $options),

            'path_asset_flag' => new \Twig_Function_Method($this, 'renderPathAssetFlag', $options),
            'path_flag'       => new \Twig_Function_Method($this, 'renderPathFlag', $options),
            'path_flags'      => new \Twig_Function_Method($this, 'renderPathFlags', $options),

            'url_asset_flag'  => new \Twig_Function_Method($this, 'renderUrlAssetFlag', $options),
            'url_flag'        => new \Twig_Function_Method($this, 'renderUrlFlag', $options),
            'url_flags'       => new \Twig_Function_Method($this, 'renderUrlFlags', $options),

            'linked_flag'     => new \Twig_Function_Method($this, 'renderLinkedFlag', $options),

            'asset_url'       => new \Twig_Function_Method($this, 'getAssetUrl')
        );
    }

    /**
     * @return array
     */
    public function getTests()
    {
        return array('assetvisible' => new \Twig_Test_Method($this, 'isAssetVisible'));
    }

    /**
     * @param $assetName
     *
     * @return bool
     */
    public function isAssetVisible($assetName)
    {
        return !in_array($this->getLocaleFromAsset($assetName), $this->hiddenLocales);
    }

    /**
     * Returns the HTML content of the image
     *
     * @param string $assetName
     * @param array  $options
     *
     * @return string
     */
    public function renderAssetFlag($assetName, array $options = array())
    {
        $asset = $this->factory->getAssetManager()->get($assetName);

        $attrs = array();
        if (isset($options['attrs'])) {
            foreach ($options['attrs'] as $key => $value) {
                if (is_array($value)) {
                    if (isset($value[$localeString = $asset->getLocaleString()])) {
                        $attrs[$key] = $value[$localeString];
                    }
                } else {
                    $attrs[$key] = $value;
                }
            }

            unset($options['attrs']);
        }

        $options = array_merge($options, array('attrs' => $attrs, 'asset' => $asset));

        return $this->renderTemplateBlock('flag', $options);
    }

    /**
     * @param string      $locale
     * @param string|null $country
     * @param array       $options
     *
     * @return string
     */
    public function renderFlag($locale, $country = null, array $options = array())
    {
        return $this->renderAssetFlag($this->getAssetName($locale, $country), $options);
    }

    /**
     * @param array $options
     *
     * @return string
     */
    public function renderFlags(array $options = array())
    {
        $options = array_merge(array('hideCurrent' => true), $options, array('flags' => $this->getAssetsNames()));

        return $this->renderTemplateBlock('flags', $options);
    }

    /**
     * @param string $route
     * @param string $assetName
     * @param array  $params
     * @param array  $options
     *
     * @return string
     */
    public function renderPathAssetFlag($route, $assetName, $params = array(), array $options = array())
    {
        /** @var $asset \ServerGrove\LocaleBundle\Asset\LocaleAsset */
        $asset = $this->factory->getAssetManager()->get($assetName);

        $options = array_merge(array('attrs' => array()), $options, array(
            'route'        => $route,
            'asset'        => $assetName,
            'route_params' => array_merge($params, array('_locale' => $asset->getLocale()))
        ));

        return $this->renderTemplateBlock('path_flag', $options);
    }

    /**
     * @param string      $route
     * @param string      $locale
     * @param array       $params
     * @param string|null $country
     * @param array       $options
     *
     * @return string
     */
    public function renderPathFlag($route, $locale, $params = array(), $country = null, array $options = array())
    {
        return $this->renderPathAssetFlag($route, $this->getAssetName($locale, $country), $params, $options);
    }

    /**
     * @param string $route
     * @param array  $params
     * @param array  $options
     *
     * @return string
     */
    public function renderPathFlags($route, $params = array(), array $options = array())
    {
        $options = array_merge(array('attrs' => array(), 'hideCurrent' => true), $options, array(
            'route'        => $route,
            'route_params' => $params,
            'flags'        => $this->getAssetsNames()
        ));

        return $this->renderTemplateBlock('path_flags', $options);
    }

    /**
     * @param string $url
     * @param string $assetName
     * @param array  $options
     *
     * @return string
     */
    public function renderUrlAssetFlag($url, $assetName, array $options = array())
    {
        return $this->renderLinkedFlag($url, $assetName, $options);
    }

    /**
     * @param string      $locale
     * @param string|null $country
     * @param array       $options
     *
     * @return string
     */
    public function renderUrlFlag($locale, $country = null, array $options = array())
    {
        $assetName = $this->getAssetName($locale, $country);

        return $this->renderUrlAssetFlag($this->getAssetUrl($assetName), $assetName, $options);
    }

    /**
     * @param array $options
     *
     * @return string
     */
    public function renderUrlFlags(array $options = array())
    {
        $options = array_merge(array('attrs'       => array(),
                                     'hideCurrent' => true), $options, array('flags' => $this->getAssetsNames()));

        return $this->renderTemplateBlock('url_flags', $options);
    }

    /**
     * @param string $url
     * @param string $assetName
     * @param array  $options
     *
     * @return string
     */
    public function renderLinkedFlag($url, $assetName, array $options = array())
    {
        $options = array_merge(array('attrs' => array()), $options, array(
            'url'   => $url,
            'asset' => $assetName,
        ));

        return $this->renderTemplateBlock('linked_flag', $options);
    }

    /**
     * @param string $assetName
     *
     * @return string
     */
    public function getAssetUrl($assetName)
    {
        $localeString = $this->getLocaleFromAsset($assetName);

        if (isset($this->domains[$localeString])) {
            return $this->domains[$localeString];
        } elseif (preg_match('/^(?P<locale>[a-z]{2})\-[A-Z]{2}$/', $localeString, $out) && isset($this->domains[$out['locale']])) {
            return $this->domains[$out['locale']];
        } else {
            return $this->domains['default'];
        }
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'flag';
    }

    /**
     * @param string $block
     * @param array  $context
     *
     * @return string
     */
    private function renderTemplateBlock($block, array $context)
    {
        $this->loadTemplate();

        return $this->template->renderBlock($block, $context);
    }

    private function loadTemplate()
    {
        if (!($this->template instanceof \Twig_Template)) {
            $this->template = $this->environment->loadTemplate($this->template);
        }
    }

    /**
     * @param string      $locale
     * @param string|null $country
     *
     * @return string
     */
    private function getAssetName($locale, $country = null)
    {
        $assetManager = $this->factory->getAssetManager();

        return $assetManager->has($assetName = $this->getAssetNameForAssetManager($locale, $country))
            ? $assetName
            : $this->getAssetNameForAssetManager($locale);
    }

    /**
     * @param string      $locale
     * @param string|null $country
     *
     * @return string
     */
    private function getAssetNameForAssetManager($locale, $country = null)
    {
        if ('locale_' == substr($locale, 0, 7) && is_null($country)) {
            return $this->getLocaleString($locale, $country);
        }

        return 'locale_'.$this->getLocaleString($locale, $country);
    }

    /**
     * @param string $locale
     * @param string $country
     *
     * @return string
     */
    private function getLocaleString($locale, $country)
    {
        return $locale.(is_null($country) ? '' : '_'.$country);
    }

    /**
     * @param string $assetName
     *
     * @return string
     */
    private function getLocaleFromAsset($assetName)
    {
        if ('locale_' != substr($assetName, 0, 7)) {
            return $assetName;
        }

        return substr($assetName, 7);
    }

    /**
     * @return array
     */
    private function getAssetsNames()
    {
        $assetManager = $this->factory->getAssetManager();
        $names        = $assetManager->getNames();

        $images = array();

        return array_filter($names, function($name) use ($assetManager, &$images) {
            $image = $assetManager->get($name)->getTargetPath();

            if (in_array($image, $images)) {
                return false;
            }

            $images[] = $image;

            return true;
        });
    }
}
