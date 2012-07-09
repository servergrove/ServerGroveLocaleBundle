<?php

namespace ServerGrove\LocaleBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use ServerGrove\LocaleBundle\Flag\LoaderInterface;

/**
 * Class LocaleListener
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class LocaleListener
{
    private $request;

    private $loader;

    /**
     * @param \Symfony\Component\HttpFoundation\Request      $request
     * @param \ServerGrove\LocaleBundle\Flag\LoaderInterface $loader
     */
    public function __construct(Request $request, LoaderInterface $loader)
    {
        $this->request = $request;
        $this->loader  = $loader;
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $languages = $this->request->getLanguages();

        do {
            $language = preg_replace('/[^a-zA-Z]+/', '-', current($languages));
            $changed  = $this->loader->forceDefault($language);
        } while (!$changed && next($languages));
    }

}
