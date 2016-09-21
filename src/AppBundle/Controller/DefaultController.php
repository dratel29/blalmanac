<?php

namespace AppBundle\Controller;

use AppBundle\Base\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DefaultController extends BaseController
{
    /**
     * @Route("/status/{criteria}", name="status", defaults={"criteria"=null})
     * @Security("has_role('ROLE_USER')")
     */
    public function statusAction(Request $request, $criteria)
    {
        $check = $this->checkToken($request);
        if ($check) {
            return new JsonResponse([
                'redirect' => $check,
            ]);
        }

        return new JsonResponse([
            'date'     => $this->get('templating')->render('AppBundle:Default:_datetime.html.twig'),
            'statuses' => $this->get('app.calendar')->getRoomsStatuses($criteria),
            'time'     => time(),
            'redirect' => null,
        ]);
    }

    /**
     * @Route("/{criteria}", name="home", defaults={"criteria"=null})
     * @Security("has_role('ROLE_USER')")
     * @Template()
     */
    public function indexAction(Request $request, $criteria)
    {
        $check = $this->checkToken($request);
        if ($check) {
            return new RedirectResponse($check);
        }

        return [
            'rooms'    => $this->get('app.google')->listRooms($criteria),
            'criteria' => $criteria,
        ];
    }

    private function checkToken(Request $request)
    {
        $token = $this->get('security.token_storage')->getToken();

        if ($token->isExpired()) {
            $this->get('security.token_storage')->setToken();

            return $this->get('hwi_oauth.security.oauth_utils')->getLoginUrl($request, 'google');
        }
    }
}
