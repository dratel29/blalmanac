<?php

namespace AppBundle\Controller;

use AppBundle\Base\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DefaultController extends BaseController
{
    /**
     * @Route("/", name="home")
     * @Template()
     * @Security("has_role('ROLE_USER')")
     */
    public function indexAction(Request $request)
    {
//        $list = $this->get('app.google')->listRooms();
//        \Symfony\Component\VarDumper\VarDumper::dump($list);
//        die();

        $list = $this->get('app.google')->listEvents('comuto.com_373335383233312d383638@resource.calendar.google.com');
        $free = $this->get('app.calendar')->getStatus($list);

        \Symfony\Component\VarDumper\VarDumper::dump($free);
        \Symfony\Component\VarDumper\VarDumper::dump($list);
        die();



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


        return array();
    }
}
