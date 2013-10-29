<?php

namespace Thumbtack\OfflinerBundle\Controller;


use FOS\ElasticaBundle\Finder\TransformedFinder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;
use Thumbtack\OfflinerBundle\Controller\BaseController;
use Thumbtack\OfflinerBundle\Models\SearchModel;

class SearchController extends BaseController {
    /**
     * @Route ("/search", name="search")
     * @Method ({"POST"})
     */
    public function searchAction(){
        $data = json_decode($this->getRequest()->getContent());
        /* @var TransformedFinder $finder */
        $finder = $this->container->get('fos_elastica.index.pages.page');
        $searcher = new SearchModel($this->getUser(), $finder,$this->getDoctrine()->getManager()->getRepository('ThumbtackOfflinerBundle:Page'));
        $results = $searcher->find($data->text);
        return new Response(json_encode($results));
    }

}