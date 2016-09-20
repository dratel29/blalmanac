<?php

namespace AppBundle\Controller;

use AppBundle\Base\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type;

class DefaultController extends BaseController
{
    /**
     * @Route("/", name="home")
     * @Template()
     * @Security("has_role('ROLE_USER')")
     */
    public function indexAction(Request $request)
    {
        $rooms    = $this->get('app.google')->listRooms();
        $criteria = $request->get('criteria');

        if (!is_null($criteria)) {
            foreach ($rooms as $email => $name) {
                if (false === strpos($name, $criteria)) {
                    unset($rooms[$email]);
                }
            }
        }

        $filter = $this
           ->createFormBuilder(['criteria' => $criteria])
           ->setMethod('GET')
           ->add('criteria', TextType::class, ['label' => 'Filter rooms'])
           ->add('Go', 'submit')
           ->getForm()
           ->createView()
        ;

        return ['filter' => $filter, 'rooms' => $rooms];


//        $list = $this->get('app.google')->listEvents('comuto.com_373335383233312d383638@resource.calendar.google.com');
//        $free = $this->get('app.calendar')->getStatus($list);
//
//        \Symfony\Component\VarDumper\VarDumper::dump($free);
//        \Symfony\Component\VarDumper\VarDumper::dump($list);
//        die();
//


//        $service = new \Google_Service_Directory($client);
//        $service->resource->
//
//        $service = new \Google_Service_Calendar($client);
//        $list = $service->calendarList->listCalendarList();
//        \Symfony\Component\VarDumper\VarDumper::dump($list);
//        die();
//
//        $calendars = [];
//        foreach ($list->getItems() as $entry) {
//            $calendars[$entry->getId()] = $entry->getSummary();
//        }

    }
}
