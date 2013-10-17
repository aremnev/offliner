<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Application\Model\Crawler;
use Application\Model\IndexerDAO;
use Application\Model\IndexerModel;
use Application\Model\PageDAO;
use Application\Model\PageSaverDAO;
use Application\Model\PageSaverModel;
use Application\Model\SitePageModel;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Http\Response;

class PageSaverController extends Controller {
    /**
     * @Route("/tasks/new", name="newTask")
     */
    public function addTaskAction(){
      $data = json_decode($this->getRequest()->getContent());
      $indexer= new PageSaverModel();
      $msg= $indexer->addTaskToQuery($data->url,intval($data->depth),($data->cl_scripts?true:false),($data->only_domain?true:false),$data->email);
      return  $this->getResponse()->setContent($msg);
    }
    /**
     * @Route("/tasks", name="newTask")
     */
    public function taskListAction(){
        $data = json_decode($this->getRequest()->getContent());
        $indexer= new PageSaverModel();
        $msg = $indexer->getTaskStatusByUser($data->email);
        return  $this->getResponse()->setContent($msg);
    }
    //TODO: tasks delete/update/check
    /**
     * @Route("/stat", name="newTask")
     */
    public function statAction(){
        $dao = new PageSaverDAO();
        $results = $dao->getSaverStat();
        return  $this->getResponse()->setContent(json_encode($results));
    }
}