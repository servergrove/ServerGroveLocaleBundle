<?php

namespace ServerGrove\LocaleBundle\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;
use ServerGrove\LocaleBundle\Flag\LoaderInterface;

/**
 * Class FlagExtension
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class FlagExtension extends \Twig_Extension
{
    /** @var \Assetic\AssetManager */
    private $container;

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
     * @param ContainerInterface                             $container
     * @param \ServerGrove\LocaleBundle\Flag\LoaderInterface $loader
     * @param string|\Twig_Template                          $template
     * @param array                                          $domains
     */
    public function __construct(ContainerInterface $container, LoaderInterface $loader, $template, array $domains)
    {
        $this->container     = $container;
        $this->loader        = $loader;
        $this->template      = $template;
        $this->domains       = $domains;
        $this->hiddenLocales = array();
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
            'domain_flag'     => new \Twig_Function_Method($this, 'renderDomainFlag', $options),
            'domains_flags'   => new \Twig_Function_Method($this, 'renderDomainsFlags', $options),

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
        $asset = $this->getAssetManager()->get($assetName);

        $attrs = array();
        if (isset($options['attrs'])) {
            $attrs = $options['attrs'];
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
        if (isset($options['attrs'])) {
            $options['attrs'] = $this->mapAttrsForLocale($options['attrs'], $this->getLocaleString($locale, $country));
        }

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
        $options = array_merge(array('attrs' => array()), $options, array(
            'route'        => $route,
            'asset'        => $assetName,
            'route_params' => array_merge($params, array('_locale' => $this->getLocaleFromAsset($assetName)))
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
     * @param string      $url
     * @param string      $locale
     * @param string|null $country
     * @param array       $options
     *
     * @return string
     */
    public function renderUrlFlag($url, $locale, $country = null, array $options = array())
    {
        return $this->renderUrlAssetFlag($url, $this->getAssetName($locale, $country), $options);
    }

    /**
     * @param string      $locale
     * @param string|null $country
     * @param array       $options
     *
     * @return string
     */
    public function renderDomainFlag($locale, $country = null, array $options = array())
    {
        return $this->renderUrlFlag($this->getAssetUrl($this->getAssetName($locale, $country)), $locale, $country, $options);
    }

    /**
     * @param array $options
     *
     * @return string
     */
    public function renderDomainsFlags(array $options = array())
    {
        $options = array_merge(array(
            'attrs'       => array(),
            'hideCurrent' => true
        ), $options, array('flags' => $this->getAssetsNames()));

        return $this->renderTemplateBlock('domains_flags', $options);
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
        $localeString = $this->getLocaleFromAsset($assetName, false);

        if (isset($this->domains[$localeString])) {
            return $this->domains[$localeString];
        } elseif (preg_match('/^(?P<locale>[a-z]{2})\-[a-z]{2}$/', strtolower($localeString), $out) && isset($this->domains[$out['locale']])) {
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
     * @return array
     */
    public function getDefaults()
    {
        return $this->loader->getDefaults();
    }

    public function forceDefault($locale)
    {
        return $this->loader->forceDefault($locale);
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
        return $this->getAssetManager()->has($assetName = $this->getAssetNameForAssetManager($locale, $country))
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
        return $locale.(is_null($country) ? '' : '_'.strtolower($country));
    }

    /**
     * @param string $assetName
     *
     * @return string
     */
    private function getLocaleFromAsset($assetName, $map = true)
    {
        if ('locale_' == substr($assetName, 0, 7)) {
            $assetName = substr($assetName, 7);
        }

        $locale = preg_replace('/[^a-zA-Z]+/', '-', $assetName);

        if ($map) {
            foreach ($this->getDefaults() as $lang => $default) {
                if ($default == $locale) {
                    return $lang;
                }
            }
        }

        return $locale;
    }

    /**
     * @return array
     */
    private function getAssetsNames()
    {
        $assetManager = $this->getAssetManager();

        $defaults   = $this->getDefaults();
        $assetNames = array();

        foreach ($defaults as $default) {
            $assetName = 'locale_'.preg_replace('/[^\w]+/', '_', $default);

            if (!$assetManager->has($assetName)) {
                throw new \RuntimeException('Missing asset for '.$assetName);
            }

            $assetNames[] = $assetName;
        }

        return $assetNames;
    }

    /**
     * @return \Assetic\AssetManager
     */
    private function getAssetManager()
    {
        return $this->container->get('assetic.asset_manager');
    }

    /**
     * @param array  $attrs
     * @param string $localeString
     *
     * @return array
     */
    private function mapAttrsForLocale(array $attrs, $localeString)
    {

        foreach ($attrs as $name => $value) {
            if (is_array($value)) {
                if (isset($value[$localeString])) {
                    $attrs[$name] = $value[$localeString];
                } elseif (isset($value['default'])) {
                    $attrs[$name] = $value['default'];
                } else {
                    unset($attrs[$name]);
                }
            }
        }

        return $attrs;
    }
}
