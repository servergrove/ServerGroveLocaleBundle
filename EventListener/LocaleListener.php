<?php

namespace ServerGrove\LocaleBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use ServerGrove\LocaleBundle\Flag\LoaderInterface;

/**
 * Class LocaleListener
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class LocaleListener
{
    /** @var \ServerGrove\LocaleBundle\Flag\LoaderInterface */
    private $loader;

    /**
     * @param \ServerGrove\LocaleBundle\Flag\LoaderInterface $loader
     */
    public function __construct(LoaderInterface $loader)
    {
        $this->loader = $loader;
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $languages = array_map(function($language) {
            return preg_replace('/[^a-zA-Z]+/', '-', $language);
        }, $event->getRequest()->getLanguages());

        $defaults = array();

        do {
            $language = current($languages);
            $lang     = current(explode('-', $language));

            if (!isset($defaults[$lang]) && $this->loader->forceDefault($language)) {
                $defaults[$lang] = $language;
            }

        } while (next($languages));
    }
}
