<?php

namespace Thumbtack\OfflinerBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;
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
      $response = new Response($msg);
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
        $user = $this->getUser();
        $response = new Response(json_encode($user->getTasks()->toArray()));
        $response->headers->set('Content-Type', 'application/json');
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
        $response = new Response($msg);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/uploads/{path}", name="getFromUploads")
     */
    public function getFromUploadsAction($path) {
        $upload_path = $this->container->getParameter('uploads_dir');
        $response = new Response(file_get_contents($upload_path.$path));
        $response->headers->set('Content-Type', 'application/zip');
        $response->setStatusCode(200); //status 'canceled' but : http://stackoverflow.com/questions/15393210/chrome-showing-canceled-on-successful-file-download-200-status
        return $response;
    }
    /**
     * @Route("/stat", name="stat")
     * @Method ({"GET"})
     */
    public function statAction(){
        /**
         * @var OfflinerModel $offliner
         */
        $offliner = $this->get("thumbtackOffliner");
        $msg = $offliner->getUserStat($this->getUser());
        $response = new Response(json_encode($msg));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}