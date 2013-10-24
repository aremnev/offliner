<?php

namespace Thumbtack\OfflinerBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Thumbtack\OfflinerBundle\Controller\BaseController;
use Thumbtack\OfflinerBundle\Entity\User;
use Thumbtack\OfflinerBundle\Models\OfflinerModel;

class OfflinerController extends BaseController {
    /**
     * @Route("/tasks/new", name="newTask")
     * @Method ({"POST"})
     */
    public function addTaskAction(){
      $data = $this->getRequest()->getContent();
    /**
     * @var OfflinerModel $offliner
     */
      $offliner= $this->get("thumbtackOffliner");
      $msg = ($offliner->addTaskToQueue($data)?"true":"false");
      $response = new \Symfony\Component\HttpFoundation\Response($msg);
      $response->headers->set('Content-Type', 'application/json');
    return $response;
    }
    /**
     * @Route("/tasks/{id}", name="updateTask")
     * @Method ({"PUT"})
     */
    public function updateTaskAction($id){
        return $this->addTaskAction();
    }
    /**
     * @Route("/tasks", name="tasksList")
     * @Method ({"POST"})
     */
    public function taskListAction(){
        /**
         * @var User $user
         */
        if($this->isUserLogged()){
            $user = $this->getUser();
            $response = new \Symfony\Component\HttpFoundation\Response(json_encode($user->getTasks()->toArray()));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }else{
            $response = new \Symfony\Component\HttpFoundation\Response('');
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
    }
    /**
     * @Route("/uploads/{path}", name="getFromUploads")
     */
    public function getFromUploadsAction($path) {
        $upload_path = '/home/istrelnikov/offliner_uploads/'; //TODO: make as parameter
        $response = new \Symfony\Component\HttpFoundation\Response(file_get_contents($upload_path.$path));
        $response->headers->set('Content-Type', 'application/zip');
        return $response;
    }

    /**
     * @Route("/tasks/{id}", requirements={"id" = "\d+"}, defaults={"id" = null} , name="taskDelete")
     * @Method ({"DELETE"})
     */
    public function taskDeleteAction($id){
        /**
         * @var OfflinerModel $offliner
         */
        $offliner = $this->get("thumbtackOffliner");
        $msg = ($offliner->deleteTaskById($id)?"true":"false");
        $response = new \Symfony\Component\HttpFoundation\Response($msg);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    //TODO: tasks update

    /**
     * @Route("/stat", name="stat")
     * @Method ({"GET"})
     */
    public function statAction(){
        /**
         * @var OfflinerModel $offliner
         */
        $offliner = $this->get("thumbtackOffliner");
        $msg = $offliner->getOfflinerStat();
        $response = new \Symfony\Component\HttpFoundation\Response(json_encode($msg));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}