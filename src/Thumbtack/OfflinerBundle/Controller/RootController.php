<?php

namespace Thumbtack\OfflinerBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Thumbtack\OfflinerBundle\Entity\User;
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
     * @Route("/login/{email}", name="login")
     */
    public function loginAction($email) {
        if ($this->isUserLogged()) {
            return $this->redirect($this->generateUrl('welcome'));
        }
        $pass = $this->getRequest()->query->get('pass');
        /**
         * @var UserProvider $userProvider
         */
        $userProvider = $this->get('thumbtackoffliner.oauth_user_provider');
        $user = $userProvider->loginUser($email,$pass);
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
        /**
         * @var UserProvider $userProvider
         */
        $userProvider = $this->get('thumbtackoffliner.oauth_user_provider');
        $user = $userProvider->registerUser($nickname,$email,$pass);
        return $this->regUserAction($user);
    }

    /**
     * @param User $user
     * @return Response
     */
    private function regUserAction($user){
        $result = array();
        if(isset($user) && $user != null ){
            $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
            $this->container->get('security.context')->setToken($token);
            $result['code'] = 0;
            $result['message'] = 'success';
        }else{
            $result['code'] = 1;
            $result['message'] = 'failed';
        }
        $response = new Response(json_encode($result));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
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
