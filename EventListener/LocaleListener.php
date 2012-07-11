<?php

namespace ServerGrove\LocaleBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use ServerGrove\LocaleBundle\Extension\FlagExtension;

/**
 * Class LocaleListener
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class LocaleListener
{
    /** @var \ServerGrove\LocaleBundle\Extension\FlagExtension */
    private $extension;

    /**
     * @param \ServerGrove\LocaleBundle\Extension\FlagExtension $extension
     */
    public function __construct(FlagExtension $extension)
    {
        $this->extension = $extension;
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $request = $event->getRequest();

        $this->extension->hideLocale($request->getLocale());

        $languages = array_map(function($language) {
            return strtolower(preg_replace('/[^a-zA-Z]+/', '-', $language));
        }, $request->getLanguages());

        $defaults = array();

        do {
            $language = current($languages);
            $lang     = current(explode('-', $language));

            if (!isset($defaults[$lang]) && $this->extension->forceDefault($language)) {
                $defaults[$lang] = $language;
            }

        } while (next($languages));
    }
}
