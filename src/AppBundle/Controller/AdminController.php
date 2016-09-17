<?php

namespace AppBundle\Controller;

use AppBundle\Base\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @Route("/admin")
 */
class AdminController extends BaseController
{
    /**
     * @Route("/boards", name="admin_baords")
     * @Template()
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function boardsAction(Request $request)
    {
//        $list = $this->get('app.google')->listRooms();
//        \Symfony\Component\VarDumper\VarDumper::dump($list);
//        die();

//        $list = $this->get('app.google')->listEvents('comuto.com_373335383233312d383638@resource.calendar.google.com');
//        $free = $this->get('app.calendar')->getStatus($list);
//
//        \Symfony\Component\VarDumper\VarDumper::dump($free);
//        \Symfony\Component\VarDumper\VarDumper::dump($list);
//        die();
//

        return array();
    }
}
