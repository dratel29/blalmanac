<?php

namespace AppBundle\Controller;

use AppBundle\Base\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;

class DefaultController extends BaseController
{

    /**
     * @Route("/status", name="status", defaults={"criteria"=null})
     * @Security("has_role('ROLE_USER')")
     */
    public function statusAction($criteria)
    {
        $rooms = $this->get('app.google')->listRooms($criteria);

        foreach ($rooms as $resourceEmail => $data) {
            $events = $this->get('app.google')->listEvents($resourceEmail);
            if ($events) {
                $availability         = $this->get('app.calendar')->getStatus($events);
                $data['availability'] = $availability;
            }
            $rooms[$resourceEmail] = $data;
        }

        return new JsonResponse($rooms);
    }

    /**
     * @Route("/{criteria}", name="home", defaults={"criteria"=null})
     * @Security("has_role('ROLE_USER')")
     * @Template()
     */
    public function indexAction($criteria)
    {
        $rooms = $this->get('app.google')->listRooms($criteria);

        foreach ($rooms as $resourceEmail => $data) {
            $events = $this->get('app.google')->listEvents($resourceEmail);
            if ($events) {
                $availability         = $this->get('app.calendar')->getStatus($events);
                $data['availability'] = $availability;
            }
            $rooms[$resourceEmail] = $data;
        }

        return [
            'rooms' => $rooms,
        ];

        // ---

        return [
            'rooms' => $this->get('app.google')->listRooms($criteria)
        ];
    }

}
