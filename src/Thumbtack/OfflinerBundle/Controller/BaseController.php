<?php

namespace Thumbtack\OfflinerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class BaseController extends Controller {
    protected function isUserLogged() {
        return (bool)$this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY');
    }
}
