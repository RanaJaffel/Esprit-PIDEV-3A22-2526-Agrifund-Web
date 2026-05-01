<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;

class LocaleListener
{
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if ($request->hasSession()) {
            $locale = $request->getSession()->get('_locale', 'fr');
            $request->setLocale($locale);
        }
    }
}
