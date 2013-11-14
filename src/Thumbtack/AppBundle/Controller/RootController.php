<?php

namespace Thumbtack\AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Thumbtack\IndexerBundle\Controller\ApiController;
use Thumbtack\IndexerBundle\Entity\Page;
use Thumbtack\AppBundle\Entity\User;
use Thumbtack\IndexerBundle\Models\Crawler;
use Thumbtack\AppBundle\Security\UserProvider;


class RootController extends BaseController {
    /**
     * @Route("/getstart", name="getstart")
     * @Route("/signIn", name="homepage")
     */
    public function signInAction() {
        if ($this->isUserLogged()) {
            return $this->redirect($this->generateUrl('welcome'));
        }
        return $this->render('ThumbtackAppBundle:Default:index.html.twig');
    }

    /**
     * @Route("/", name="welcome")
     */
    public function welcomeAction() {
        $key = base64_encode(ApiController::strcode($this->getUser()->getEmail(), $this->container->getParameter('secret'))); //TODO:remove strcode from here
        return $this->render('ThumbtackAppBundle:Default:welcome.html.twig', array('user_api_key' => $key));
    }

    /**
     * @Route("/login/{email}", name="login")
     */
    public function loginAction($email) { //TODO: DIVIDE AJAX AND NOT-AJAX CALLS
        if ($this->isUserLogged()) {
            return $this->redirect($this->generateUrl('welcome'));
        }
        $pass = $this->getRequest()->query->get('pass');
        /** @var UserProvider $userProvider */
        $userProvider = $this->get('thumbtackApp.oauth_user_provider');
        $user = $userProvider->loginUser($email, $pass);
        return $this->regUserAction($user);
    }

    /**
     * @Route("/register/{email}", name="register")
     */
    public function registerAction($email) {
        if ($this->isUserLogged()) {
            return $this->redirect($this->generateUrl('welcome'));
        }
        $nickname = $this->getRequest()->query->get('nick');
        $pass = $this->getRequest()->query->get('pass');
        /** @var UserProvider $userProvider */
        $userProvider = $this->get('thumbtackApp.oauth_user_provider');
        $user = $userProvider->registerUser($nickname, $email, $pass);
        return $this->regUserAction($user);
    }

    /**
     * Supply method
     * @param User $user
     * @return Response
     */
    private function regUserAction($user) {
        $response = new Response();
        $response->setStatusCode(401);
        if (!empty($user)) {
            $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
            $this->container->get('security.context')->setToken($token);
            $response->setStatusCode(200);
        }
        return $response;
    }

    /**
     * @Route("/about", name="about")
     * @Route("/userstat", name="userstat")
     * @Route("/profile", name="profile")
     * @Route("/recover", name="recover")
     * @Route("/contacts", name="contacts")
     * @Route("/quicktour", name="quicktour")
     * @Route("/api", name="api")
     * @Route("/demo", name="demo")
     */
    public function todoAction() {
        return $this->render('ThumbtackAppBundle:Default:todo.html.twig');
    }

}
