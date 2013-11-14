<?php

namespace Thumbtack\IndexerBundle\Controller;


use FOS\ElasticaBundle\Finder\TransformedFinder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Thumbtack\IndexerBundle\Entity\Page;
use Thumbtack\AppBundle\Entity\User;
use Thumbtack\IndexerBundle\Models\IndexerModel;
use Thumbtack\IndexerBundle\Models\IndexerProcessor;

class IndexerController extends Controller {


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
            $results = $searcher->find($data->text, (!empty($data->domain) ? $data->domain : null));
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
        /** @var User $user */ //TODO: create interface to work with this bundle
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
        $page = $this->getDoctrine()->getManager()->getRepository('ThumbtackIndexerBundle:Page')->findOneByHashUrl($hash);
        return $this->render('ThumbtackIndexerBundle:Default:preview.html.php', array('page' => $page));
    }
}