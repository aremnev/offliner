<?php

namespace Thumbtack\OfflinerBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;


class RootController extends BaseController {
    /**
     * @Route("/signIn", name="homepage")
     */
    public function signInAction() {

        if ($this->isUserLogged()) {
            return $this->redirect($this->generateUrl('welcome'));
        }
        $offliner = $this->get('thumbtackOffliner');

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
