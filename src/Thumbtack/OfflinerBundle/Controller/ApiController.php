<?php

namespace Thumbtack\OfflinerBundle\Controller;


use FOS\ElasticaBundle\Finder\TransformedFinder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;
use Thumbtack\OfflinerBundle\Controller\BaseController;
use Thumbtack\OfflinerBundle\Entity\Page;
use Thumbtack\OfflinerBundle\Entity\User;
use Thumbtack\OfflinerBundle\Models\IndexerModel;
use Thumbtack\OfflinerBundle\Models\ServiceProcessor;

class ApiController extends BaseController {
    public static function strcode($str, $passw="")
    {
        $salt = "Dn8*#2nBNKJB:LH:LJHG:JGLHJ:LGAFOUGQHODSG:KLN(*%&^$!9j";
        $len = strlen($str);
        $gamma = '';
        $n = $len>100 ? 8 : 2;
        while( strlen($gamma)<$len )
        {
            $gamma .= substr(pack('H*', sha1($passw.$gamma.$salt)), 0, $n);
        }
        return $str^$gamma;
    }

    /**
     * @Route ("/api/search/{key}", name="apiSearch")
     * @Method ({"POST"})
     */
    public function apiSearchAction($key){
        /*$txt = "Hello XOR encode!";
        $txt = base64_encode(strcode($txt, 'mypassword'));
        echo $txt;

        $txt = "ZOHdWKf+cf7vAwpJNfSJ8s8=";
        $txt = strcode(base64_decode($txt), 'mypassword');
        echo $txt;
        */
        $results = array();
        $email = ApiController::strcode(base64_decode($key), $this->container->getParameter('secret'));
        $data = $this->getRequest()->get('search');
        $user = $this->getDoctrine()->getRepository('ThumbtackOfflinerBundle:User')->findOneByEmail($email);
        if(!empty($data) && !empty($user)){
            /* @var TransformedFinder $finder */
            $index = $this->container->get('fos_elastica.index.pages.page');
            $searcher = new IndexerModel($user,$index,$this->getDoctrine()->getManager());
            $domain = $this->getRequest()->get('domainId');
            $results = $searcher->find($data,$domain);
        }
        return new Response(json_encode($results));
    }
}