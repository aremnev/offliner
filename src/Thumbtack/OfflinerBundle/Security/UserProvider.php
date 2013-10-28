<?php

namespace Thumbtack\OfflinerBundle\Security;

use Doctrine\ORM\EntityManager;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUserProvider as BaseOAuthUserProvider;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Thumbtack\OfflinerBundle\Entity\User;

class UserProvider extends BaseOAuthUserProvider {
    /**
     * @var EntityManager
     */
    protected $em;

    function __construct($doctrine) {
        $this->em = $doctrine->getManager();
    }
    /**
     * RegisterUser
     */
    public function registerUser($nickname,$email,$pass) {
        $repository = $this->em->getRepository('ThumbtackOfflinerBundle:User');
        $user = $repository->findOneByEmail($email);
        if(!isset($user)){
            $user = new User();
            $user->setUsername($nickname);
            $user->setNickname($nickname);
            $user->setEmail($email);
            $user->setPassword(md5($pass));
            //TODO: gravatar?
            $user->setJoinDate(new \DateTime("now"));
            $this->em->persist($user);
            $this->em->flush();
            return $user;
        }else{
            return null;
        }
    }
    /**
     * LoginUser
     */
    public function loginUser($email,$pass) {
        $repository = $this->em->getRepository('ThumbtackOfflinerBundle:User');
        /**
         * @var User $user
         */
        $user = $repository->findOneByEmail($email);
        if (isset($user) && $user->getPassword() == md5($pass)) {
            return $user;
        } else {
            return null;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function loadUserByUsername($email) {
        $repository = $this->em->getRepository('ThumbtackOfflinerBundle:User');
        $user = $repository->findOneByEmail($email);
        if (isset($user)) {
            return $user;
        } else {
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response) {
        $repository = $this->em->getRepository('ThumbtackOfflinerBundle:User');
        $user = $repository->findOneByEmail($response->getEmail());
        if (isset($user)) {
            return $user;
        } else {
            $user = new User();
            $user->setUsername($response->getEmail());

            if ($response->getNickname()) {
                $user->setNickname($response->getNickname());
            } else {
                $user->setNickname($response->getEmail());
            }
            $user->setPassword(md5($response->getEmail()));
            $user->setEmail($response->getEmail());
            if ($response->getProfilePicture() != null) {
                $user->setPhoto($response->getProfilePicture());
            }
            $user->setJoinDate(new \DateTime("now"));
            $this->em->persist($user);
            $this->em->flush();
            return $user;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function refreshUser(UserInterface $user) {
        if (!$this->supportsClass(get_class($user))) {
            throw new UnsupportedUserException(sprintf('Unsupported user class "%s"', get_class($user)));
        }
        return $this->loadUserByUsername($user->getEmail());
    }

    /**
     * {@inheritDoc}
     */
    public function supportsClass($class) {
        return $class === 'Thumbtack\\OfflinerBundle\\Entity\\User';
    }
}
