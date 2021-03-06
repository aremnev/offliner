<?php

namespace Thumbtack\OfflinerBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Thumbtack\AppBundle\Entity\User;
use Thumbtack\OfflinerBundle\Models\OfflinerModel;

class OfflinerController extends Controller {
    /**
     * @Route("/tasks/new", name="newTask")
     * @Method ({"POST"})
     */
    public function addTaskAction() {
        $data = $this->getRequest()->getContent();
        $response = new Response();
        $response->setStatusCode(500);
        if (!empty($data)) {
            /** @var OfflinerModel $offliner */
            $offliner = $this->get("thumbtackOffliner");
            if ($offliner->addTask($data)) {
                $response->headers->set('Content-Type', 'application/json');
                $response->setStatusCode(202);
            }
        }
        return $response;
    }

    /**
     * @Route("/tasks/{id}", name="updateTask")
     * @Method ({"PUT"})
     */
    public function updateTaskAction($id) {
        return $this->addTaskAction();
    }

    /**
     * @Route("/tasks", name="tasksList")
     * @Method ({"GET"})
     */
    public function taskListAction() {
        /** @var User $user */
        $user = $this->getUser();
        $response = new Response(json_encode($user->getTasks()->toArray()));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/tasks/{id}", requirements={"id" = "\d+"}, defaults={"id" = null} , name="taskDelete")
     * @Method ({"DELETE"})
     */
    public function taskDeleteAction($id) {
        /** @var OfflinerModel $offliner */
        $offliner = $this->get("thumbtackOffliner");
        $response = new Response();
        $response->setStatusCode($offliner->deleteTaskById($id) ? 204 : 404);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/uploads/{path}", name="getFromUploads")
     */
    public function getFromUploadsAction($path) {
        $upload_path = $this->container->getParameter('uploads_dir');
        $response = new Response(file_get_contents($upload_path . $path));
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $path . '"');
        return $response;
    }

    /**
     * @Route("/stat", name="stat")
     * @Method ({"GET"})
     */
    public function statAction() {
        /** @var OfflinerModel $offliner */
        $offliner = $this->get("thumbtackOffliner");
        $msg = $offliner->getUserStat($this->getUser());
        $response = new Response(json_encode($msg));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}