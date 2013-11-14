<?php

namespace Thumbtack\IndexerBundle\Controller;


use FOS\ElasticaBundle\Finder\TransformedFinder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Thumbtack\IndexerBundle\Entity\Page;
use Thumbtack\IndexerBundle\Models\IndexerModel;

class ApiController extends Controller {
    public static function strcode($str, $passw = "") {
        $salt = "Dn8*#2nBNK";
        $len = strlen($str);
        $gamma = '';
        $n = $len > 100 ? 8 : 2;
        while (strlen($gamma) < $len) {
            $gamma .= substr(pack('H*', sha1($passw . $gamma . $salt)), 0, $n);
        }
        return $str ^ $gamma;
    }

    /**
     * @Route ("/api/search/json/{key}", name="apiJSONSearch")
     * @Method ({"GET"})
     */
    public function apiSearchJSONAction($key) {
        $results = array();
        $email = ApiController::strcode(base64_decode($key), $this->container->getParameter('secret'));
        $data = $this->getRequest()->get('search');
        $user = $this->getDoctrine()->getRepository('ThumbtackAppBundle:User')->findOneByEmail($email); //TODO:replace by class name from config and create interface
        if (!empty($data) && !empty($user)) {
            /* @var TransformedFinder $finder */
            $index = $this->container->get('fos_elastica.index.pages.page');
            $searcher = new IndexerModel($user, $index, $this->getDoctrine()->getManager());
            $domain = $this->getRequest()->get('domainId');
            $results = $searcher->find($data, $domain);
        }
        return new Response(json_encode($results));
    }
    /**
      * @Route ("/api/search/html/{key}", name="apiHTMLSearch")
      * @Method ({"GET"})
    */
    public function apiSearchHTMLAction($key) {
        $results = array();
        $email = ApiController::strcode(base64_decode($key), $this->container->getParameter('secret'));
        $data = $this->getRequest()->get('search');
        $user = $this->getDoctrine()->getRepository('ThumbtackAppBundle:User')->findOneByEmail($email); //TODO:replace by class name from config and create interface
        if (!empty($data) && !empty($user)) {
            /* @var TransformedFinder $finder */
            $index = $this->container->get('fos_elastica.index.pages.page');
            $searcher = new IndexerModel($user, $index, $this->getDoctrine()->getManager());
            $domain = $this->getRequest()->get('domain');
            $results = $searcher->find($data, $domain);
        }
        return $this->render('ThumbtackIndexerBundle:Default:searchResult.html.php', array('results' => $results));
    }
}