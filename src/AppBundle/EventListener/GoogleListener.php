<?php

namespace AppBundle\EventListener;

use Fuz\QuickStartBundle\Base\BaseService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;

class GoogleListener extends BaseService implements EventSubscriberInterface
{
    public function onRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $symfonyToken = $this->get('security.token_storage')->getToken();
        if ($symfonyToken instanceof AnonymousToken) {
            return;
        }

        if ($this->get('app.google')->checkTokenExpiration()) {
            return;
        }

        $this->get('security.token_storage')->setToken();

        $event->setResponse(
            new RedirectResponse(
                $this->get('hwi_oauth.security.oauth_utils')->getLoginUrl($event->getRequest(), 'google')
            )
        );
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array(array('onRequest', 5)),
        );
    }
}
