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

class IndexerController extends BaseController {


    /**
     * @Route ("/search", name="search")
     * @Method ({"POST"})
     */
    public function searchAction() {
        // Review: add validation on null --Resolved
        $data = json_decode($this->getRequest()->getContent());
        /* @var TransformedFinder $finder */
        $results = '';
        if (!empty($data)) {
            $index = $this->container->get('fos_elastica.index.pages.page');
            $searcher = new IndexerModel($this->getUser(), $index, $this->getDoctrine()->getManager());
            $results = $searcher->find($data->text, $data->$domain);
        }
        return new Response(json_encode($results));
    }

    /**
     * @Route("/domains/new", name="newDomain")
     * @Method ({"POST"})
     */
    public function addDomainkAction() {
        $json = $this->getRequest()->getContent();
        $response = new Response();
        $response->setStatusCode(500);
        if (!empty($json)) { //TODO: json fields validation
            /** @var IndexerModel $indexer */
            $index = $this->container->get('fos_elastica.index.pages.page');
            $indexer = new IndexerModel($this->getUser(), $index, $this->getDoctrine()->getManager());
            // Review: $indexer->addDomain($data) already return bool value --Resolved
            if ($indexer->addDomain($json)) {
                $response->setStatusCode(201);
                $response->headers->set('Content-Type', 'application/json');
            }
        }
        return $response;
    }

    /**
     * @Route("/domains", name="domainsList")
     * @Method ({"GET"})
     */
    public function domainsListAction() {
        /** @var User $user */
        $user = $this->getUser();
        $response = new Response(json_encode($user->getDomains()->toArray()));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/domains/{id}", requirements={"id" = "\d+"}, defaults={"id" = null} , name="domainsDelete")
     * @Method ({"DELETE"})
     */
    public function domainDeleteAction($id) {
        /** @var IndexerModel $indexer */
        $index = $this->container->get('fos_elastica.index.pages.page');
        $indexer = new IndexerModel($this->getUser(), $index, $this->getDoctrine()->getManager());
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode($indexer->deleteDomainById($id) ? 204 : 404);
        return $response;
    }

    /**
     * @Route("/domains/{id}", requirements={"id" = "\d+"}, defaults={"id" = null} , name="getDomainInfo")
     * @Method ({"GET"})
     */
    public function getDomainInfoAction($id) {
        /** @var IndexerModel $indexer */
        $index = $this->container->get('fos_elastica.index.pages.page');
        $indexer = new IndexerModel($this->getUser(), $index, $this->getDoctrine()->getManager());
        $response = new Response($indexer->getDomainInfo($id));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/preview/{hash}", name="savedCopy")
     * @Method ({"GET"})
     */
    public function savedCopyAction($hash) {
        // Review: move to view --Resolved
        $page = $this->getDoctrine()->getManager()->getRepository('ThumbtackOfflinerBundle:Page')->findOneByHashUrl($hash);
        return $this->render('ThumbtackOfflinerBundle:Default:preview.html.php', array('page' => $page));
    }
}