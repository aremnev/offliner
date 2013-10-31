<?php
/**
 * Created by JetBrains PhpStorm.
 * User: istrelnikov
 * Date: 9/20/13
 * Time: 8:42 PM
 * To change this template use File | Settings | File Templates.
 */
namespace Thumbtack\OfflinerBundle\Models;
//TODO: error codes/messages
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Config\Definition\Exception\Exception;
use Thumbtack\OfflinerBundle\Entity\Task;
use Thumbtack\OfflinerBundle\Entity\User;

class OfflinerModel {
    //----constants
    /**
     * @var EntityManager
     */
    private  $dm;
    /**
     * @var User
     */
    private  $user;
    /**
     * @var EntityRepository
     */
    private $tasksRepo;

    /**
     * @param $secure
     * @param $doctrine
     */
    function __construct($secure,$doctrine){
        $this->dm = $doctrine->getManager();
        $this->user = $secure->getToken()->getUser();
        $this->tasksRepo = $this->dm->getRepository('ThumbtackOfflinerBundle:Task');
    }

    function addTaskToQueue($json){
        try{
            $data = json_decode($json,true);
            $data['url']= ServiceProcessor::prepareURL($data['url']);
            $data['status'] = ServiceProcessor::STATUS_AWAITING;
            if(!isset($data['id'])){
                $task = new Task($data);
                $task->setUser($this->user);
                $this->dm->persist($task);
                $this->dm->flush();
                return true;
            }else{  //
                /**
                 * @var Task $task
                 */
                $task = $this->tasksRepo->findOneById($data['id']);
                $task->setUser($this->user);
                $task->setClearScripts($data['clearScripts']);
                $task->setMaxDepth($data['maxDepth']);
                $task->setDate(new \DateTime());
                $task->setOnlyDomain($data['onlyDomain']);
                $task->setUrl($data['url']);
                $this->dm->persist($task);
                $this->dm->flush();
                return true;
            }
        }catch (Exception $e){
            return false;
        }
    }
    function deleteTaskById($id){
        $task = $this->tasksRepo->findOneById($id);
        if($task){
            $this->dm->remove($task);
            $this->dm->flush();
            return true;
        }else{
            return false;
        }
    }

    public function getUserStat($user){
        $query = $this->dm->createQuery('SELECT count(t) FROM ThumbtackOfflinerBundle:Task t WHERE t.status = ?1 AND t.user = ?2');
        $query->setParameter(2, $user);
        $query->setParameter(1, ServiceProcessor::STATUS_AWAITING);
        $result['queue'] = $query->getSingleScalarResult();
        $query->setParameter(1, ServiceProcessor::STATUS_PROGRESS);
        $result['progress'] = $query->getSingleScalarResult();
        $query->setParameter(1, ServiceProcessor::STATUS_READY);
        $result['done'] = $query->getSingleScalarResult();
        return $result;
    }

}