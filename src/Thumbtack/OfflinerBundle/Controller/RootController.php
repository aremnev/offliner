<?php

namespace Thumbtack\OfflinerBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Thumbtack\OfflinerBundle\Entity\User;
use Thumbtack\OfflinerBundle\Security\TempResponse;
use Thumbtack\OfflinerBundle\Security\UserProvider;


class RootController extends BaseController {
    /**
     * @Route("/signIn", name="homepage")
     */
    public function signInAction() {

        if ($this->isUserLogged()) {
            return $this->redirect($this->generateUrl('welcome'));
        }
        return $this->render('ThumbtackOfflinerBundle:Default:index.html.twig');
    }

    /**
     * @Route("/", name="welcome")
     */
    public function welcomeAction() {
        if (!$this->isUserLogged()) {
            return $this->redirect($this->generateUrl('homepage'));
        }
        return $this->render('ThumbtackOfflinerBundle:Default:welcome.html.twig');
    }
    /**
     * @Route("/tmplogin", name="tmplogin")
     */
    public function logUser() {
        if ($this->isUserLogged()) {
            return $this->redirect($this->generateUrl('welcome'));
        }
        /**
         * @var UserProvider $userProvider
         */
        $userProvider = $this->get('thumbtackoffliner.oauth_user_provider');
        $tempResp = new TempResponse();
        $tmpUser = $userProvider->loadUserByOAuthUserResponse($tempResp);
        $token = new UsernamePasswordToken($tmpUser, null, 'main', $tmpUser->getRoles());
        $this->container->get('security.context')->setToken($token);
        return $this->redirect($this->generateUrl('welcome'));
    }
    /**
     * @Route("/about", name="about")
     */
    public function phpinfoAction() {

        return $this->render('ThumbtackOfflinerBundle:Default:about.html.php');
    }

    /**
     * @Route("/users", name="userlist")
     */
    public function getUserListAction() {
        $repository = $this->getDoctrine()->getRepository('ThumbtackOfflinerBundle:User');
        $users = $repository->findAll();

        if (!$users) {
            throw $this->createNotFoundException('No users Found');
        }
        $resp = 'User names: ';
        foreach ($users as $user) $resp .= " -> " . $user->getUsername();
        return new Response($resp);
    }
}
