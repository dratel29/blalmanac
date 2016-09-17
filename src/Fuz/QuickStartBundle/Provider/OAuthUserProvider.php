<?php

namespace Fuz\QuickStartBundle\Provider;

use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUserProvider as BaseUserProvider;

class OAuthUserProvider extends BaseUserProvider
{
    protected $userRepository;
    protected $userManager;
    protected $admins;

    public function __construct(EntityManagerInterface $em, UserManagerInterface $userManager, array $admins)
    {
        $this->userRepository = $em->getRepository('FuzQuickStartBundle:User');
        $this->userManager    = $userManager;
        $this->admins         = $admins;
    }

    public function loadUserByUsername($username)
    {
        list($resourceOwner, $resourceOwnerId) = json_decode($username);

        return $this->userRepository->getUserByResourceOwnerId($resourceOwner, $resourceOwnerId);
    }

    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $resourceOwner   = $response->getResourceOwner()->getName();
        $resourceOwnerId = $response->getUsername();
        $name            = $this->getNameToDisplay($resourceOwner, $response);
        $contact         = $response->getEmail();
        $json            = json_encode(array($resourceOwner, $resourceOwnerId));

        $user = $this->userRepository->getUserByResourceOwnerId($resourceOwner, $resourceOwnerId);

        if (is_null($user)) {
            $user = $this->userManager->createUser();
            $user->setUsername($json);
            $user->setEnabled(true);
            $user->setResourceOwner($resourceOwner);
            $user->setResourceOwnerId($resourceOwnerId);
            $user->setNickname($name);
            $user->setContact($contact);
            $user->setSigninCount(1);
            $this->userManager->updateUser($user);
            $user = $this->loadUserByUsername($json);
        } else {
            $user->setNickname($name);
            $user->setContact($contact);
            $user->setSigninCount($user->getSigninCount() + 1);
            $this->userManager->updateUser($user);
        }

        if (in_array($user->getContact(), $this->admins) && !$user->hasRole('ROLE_ADMIN')) {
            // ¯\_(ツ)_/¯
            $user->addRole('ROLE_ADMIN');
        }

        return $user;
    }

    public function getNameToDisplay($resourceOwner, $response)
    {
        $name = null;
        switch ($resourceOwner) {
            case 'google':
                $name = $response->getNickname();
                break;
            case 'facebook':
                $name = $response->getRealName();
                break;
            case 'twitter':
                $name = $response->getNickname();
                break;
            default:
                break;
        }

        return $name;
    }

    public function supportsClass($class)
    {
        return $class === 'Fuz\\QuickStartBundle\\Entity\\User';
    }
}
