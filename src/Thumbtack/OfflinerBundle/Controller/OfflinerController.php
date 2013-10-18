<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Thumbtack\OfflinerBundle\Models\OfflinerModel;

class PageSaverController extends Controller {
    /**
     * @Route("/tasks/new", name="newTask")
     */
    public function addTaskAction(){
      $data = json_decode($this->getRequest()->request->all());
      $offliner= new OfflinerModel($this->getDoctrine()->getManager());
      $msg= $offliner->addTaskToQuery($data);
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