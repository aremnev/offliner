<?php

namespace Thumbtack\OfflinerBundle\Controller;


use FOS\ElasticaBundle\Finder\TransformedFinder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;
use Thumbtack\OfflinerBundle\Controller\BaseController;
use Thumbtack\OfflinerBundle\Models\IndexerModel;
use Thumbtack\OfflinerBundle\Models\SearchModel;

class IndexerController extends BaseController {
    /**
     * @Route ("/search", name="search")
     * @Method ({"POST"})
     */
    public function searchAction(){
        $data = json_decode($this->getRequest()->getContent());
        /* @var TransformedFinder $finder */
        $index = $this->container->get('fos_elastica.index.pages.page');
        $searcher = new IndexerModel($this->getUser(),$index,$this->getDoctrine()->getManager());
        $results = $searcher->find($data->text);
        return new Response(json_encode($results));
    }

}