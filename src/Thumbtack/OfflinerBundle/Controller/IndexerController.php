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
    public function searchAction(){
        $data = json_decode($this->getRequest()->getContent());
        /* @var TransformedFinder $finder */
        $index = $this->container->get('fos_elastica.index.pages.page');
        $searcher = new IndexerModel($this->getUser(),$index,$this->getDoctrine()->getManager());
        $domain = (isset($data->domainId)?$data->domainId:null);
        $results = $searcher->find($data->text,$domain);
        return new Response(json_encode($results));
    }
    /**
     * @Route("/domains/new", name="newDomain")
     * @Method ({"POST"})
     */
    public function addDomainkAction(){
        $data = $this->getRequest()->getContent();
        /**
         * @var IndexerModel $indexer
         */
        $index = $this->container->get('fos_elastica.index.pages.page');
        $indexer = new IndexerModel($this->getUser(),$index,$this->getDoctrine()->getManager());
        $msg = ($indexer->addDomain($data)?"true":"false");
        $response = new Response($msg);
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode(201);
        return $response;
    }
    /**
     * @Route("/domains", name="domainsList")
     * @Method ({"GET"})
     */
    public function domainsListAction(){
        /**
         * @var User $user
         */
        $user = $this->getUser();
        $response = new Response(json_encode($user->getDomains()->toArray()));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/domains/{id}", requirements={"id" = "\d+"}, defaults={"id" = null} , name="domainsDelete")
     * @Method ({"DELETE"})
     */
    public function domainDeleteAction($id){
        /**
         * @var IndexerModel $indexer
         */
        $index = $this->container->get('fos_elastica.index.pages.page');
        $indexer = new IndexerModel($this->getUser(),$index,$this->getDoctrine()->getManager());
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode($indexer->deleteDomainById($id)?204:404);
        return $response;
    }
    /**
     * @Route("/domains/{id}", requirements={"id" = "\d+"}, defaults={"id" = null} , name="getDomainInfo")
     * @Method ({"GET"})
     */
    public function getDomainInfoAction($id){
        /**
         * @var IndexerModel $indexer
         */
        $index = $this->container->get('fos_elastica.index.pages.page');
        $indexer = new IndexerModel($this->getUser(),$index,$this->getDoctrine()->getManager());
        $response = new Response($indexer->getDomainInfo($id));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
    /**
     * @Route("/preview/{hash}", name="savedCopy")
     * @Method ({"GET"})
     */
    public function savedCopyAction($hash){
        /** @var Page $page */
        $page = $this->getDoctrine()->getManager()->getRepository('ThumbtackOfflinerBundle:Page')->findOneByHashUrl($hash);
        $response='<h1 style="text-align: center">This page have status: ';
        switch($page->getStatus()){
            case ServiceProcessor::STATUS_AWAITING;
                $response.=' in index query</h1>';
                break;
            case ServiceProcessor::STATUS_PROGRESS:
                $response.=' indexing in process</h1>';
                break;
            case ServiceProcessor::STATUS_READY:
                $response =
                    '<div style="background:#fff;border:1px solid #999;margin:-1px -1px 0;padding:0;">
                    <div style="background:#eee;border:1px solid #fefefe;color:#000;font:13px arial,sans-serif;font-weight:normal;margin:3px;padding:5px;text-align:left">
                    This is saved version by tt-indexer service. Original content may be changed (Last indexed:'.$page->getDate()->format('Y-m-d').')</div>
                    </div>'.
                    '<div style="position:relative">'.$page->getHtml().'</div>';
                break;
            default:
                $response.=' not in database</h1>';
        }
        return new Response($response);
    }
}