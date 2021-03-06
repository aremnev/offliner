<?php
namespace Thumbtack\OfflinerBundle\Models;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Config\Definition\Exception\Exception;
use Thumbtack\OfflinerBundle\Entity\OfflinerUserInterface;
use Thumbtack\OfflinerBundle\Entity\Task;
use Thumbtack\AppBundle\Entity\User;

require_once(__DIR__ . '/../Misc/normilize_url.php');
class OfflinerModel {
    /** @var EntityManager */
    private $dm;
    /** @var User */
    private $user;
    /** @var EntityRepository */
    private $tasksRepo;

    /**
     * @param $secure
     * @param $doctrine
     * @throws \Symfony\Component\Config\Definition\Exception\Exception
     */
    function __construct($secure, $doctrine) {
        $this->dm = $doctrine->getManager();
        $this->user = $secure->getToken()->getUser();
        if(!($this->user instanceof OfflinerUserInterface)){
            throw new Exception('constructor param User must implements OfflinerUserInterface');
        }
        $this->tasksRepo = $this->dm->getRepository('ThumbtackOfflinerBundle:Task');
    }

    function addTask($json) {
        try {
            if (empty($json)) { // {url,status,maxDepth,clearScripts,onlyDomain} validation???
                return false;
            }
            $data = json_decode($json, true);
            $data['url'] = normilize_url($data['url']);
            $data['status'] = OfflinerProcessor::STATUS_AWAITING;
            if (empty($data['id'])) { //create
                $task = new Task();
                $task->setUser($this->user);
                $task->setStatus($data['status']);
                $task->setClearScripts($data['clearScripts']);
                $task->setMaxDepth($data['maxDepth']);
                $task->setOnlyDomain($data['onlyDomain']);
                $task->setUrl($data['url']);
                $this->dm->persist($task);
                $this->dm->flush();
                return true;
            } else { //update
                /** @var Task $task */
                $task = $this->tasksRepo->findOneById($data['id']);
                $task->setUser($this->user);
                $task->setStatus($data['status']);
                $task->setClearScripts($data['clearScripts']);
                $task->setMaxDepth($data['maxDepth']);
                $task->setDate(new \DateTime());
                $task->setOnlyDomain($data['onlyDomain']);
                $task->setUrl($data['url']);
                $this->dm->persist($task);
                $this->dm->flush();
                return true;
            }
        } catch (Exception $e) {
            //suppress&log
            return false;
        }
    }

    function deleteTaskById($id) {
        $task = $this->tasksRepo->findOneById($id);
        if ($task) {
            $this->dm->remove($task);
            $this->dm->flush();
            return true;
        } else {
            return false;
        }
    }

    public function getUserStat($user) {
        $query = $this->dm->createQuery('SELECT count(t) FROM ThumbtackOfflinerBundle:Task t WHERE t.status = ?1 AND t.user = ?2');
        $query->setParameter(2, $user);
        $query->setParameter(1, OfflinerProcessor::STATUS_AWAITING);
        $result['queue'] = $query->getSingleScalarResult();
        $query->setParameter(1, OfflinerProcessor::STATUS_PROGRESS);
        $result['progress'] = $query->getSingleScalarResult();
        $query->setParameter(1, OfflinerProcessor::STATUS_READY);
        $result['done'] = $query->getSingleScalarResult();
        return $result;
    }

}