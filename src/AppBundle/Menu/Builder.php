<?php

namespace AppBundle\Menu;

use Fuz\QuickStartBundle\Base\BaseMenu;
use Knp\Menu\FactoryInterface;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;

class Builder extends BaseMenu
{
   public function userLeftMenu(FactoryInterface $factory, array $options)
    {
        $menu = $this->createMenu($factory, parent::POSITION_LEFT);
        $this->addRoute($menu, 'quickstart.menu.home', 'home');

        $token = $this->container->get('security.token_storage')->getToken();
        if ($token instanceof OAuthToken && $token->getUser()->hasRole('ROLE_ADMIN')) {

            $this->addRoute($menu, 'Boards', 'admin_baords');

        }



        /*
          $this->addSubMenu($menu, 'test');
          $this->addRoute($menu['test'], 'testA', 'testa');
          $this->addRoute($menu['test'], 'testB', 'testb', array(), array(), true);
          $this->addRoute($menu['test'], 'testC', 'testc');
        */

        return $menu;
    }
}
