<?php

namespace AppBundle\Controller;

use AppBundle\Base\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends BaseController
{
    /**
     * @Route("/status/{criteria}", name="status", defaults={"criteria"=null}, requirements={"criteria"=".+"})
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

        $ipad = false;
        if ($request->get('ipad')) {
            $ipad = true;
        }

        return new JsonResponse([
            'body' => $this->get('templating')->render('AppBundle:Default:_body.html.twig', [
                'rooms' => $this->get('app.calendar')->getRoomsStatuses($criteria),
                'ipad' => intval($ipad),
            ]),
            'redirect' => null,
        ]);
    }

    /**
     * @Route("/book", name="book")
     * @Security("has_role('ROLE_USER')")
     */
    public function bookAction(Request $request)
    {
        $email = $request->request->get('email');
        $time = $request->request->get('time');

        if ($this->getParameter('booking_enabled')) {
            $this->get('app.google')->book($email, $time);
        }

        return new Response();
    }

    /**
     * @Route("/{criteria}", name="home", defaults={"criteria"=null}, requirements={"criteria"=".+"})
     * @Security("has_role('ROLE_USER')")
     * @Template()
     */
    public function indexAction(Request $request, $criteria)
    {
        $check = $this->checkToken($request);
        if ($check) {
            return new RedirectResponse($check);
        }

        $ipad = false;
        if ($request->get('ipad')) {
            $ipad = true;
        }

        return [
            'rooms' => $this->get('app.google')->listRooms($criteria),
            'criteria' => $criteria,
            'ipad' => intval($ipad),
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
