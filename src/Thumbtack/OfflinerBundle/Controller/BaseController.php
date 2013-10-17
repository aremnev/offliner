<?php

namespace Thumbtack\OfflinerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class BaseController extends Controller {
    protected function isUserLogged() {
        if ($this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return true;
        } else {
            return false;
        }
    }

}
