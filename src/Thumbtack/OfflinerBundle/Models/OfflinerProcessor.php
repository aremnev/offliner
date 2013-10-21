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
use Thumbtack\OfflinerBundle\Entity\Process;
use Thumbtack\OfflinerBundle\Entity\Task;
use Thumbtack\OfflinerBundle\Entity\User;

class OfflinerProcessor {
    /**
     * @var EntityManager
     */
    private  $dm;
    /**
     * @var EntityRepository
     */
    private $tasksRepo;
    /**
     * @var EntityRepository
     */
    private $serviceRepo;
    private $maxProcessCount;
    /**
     * @var Process
     */
    private $process;

    /**
     * @param $doctrine
     * @param integer $mpc
     */
    function __construct($doctrine,$mpc){
        $this->dm = $doctrine->getManager();
        $this->tasksRepo = $this->dm->getRepository('ThumbtackOfflinerBundle:Task');
        $this->serviceRepo = $this->dm->getRepository('ThumbtackOfflinerBundle:Process');
        $this->maxProcessCount = $mpc;
    }
    public function runQueryTask(){
        if($this->regProcess()){
            /**
             * @var Task $task;
             */
            $task = $this->tasksRepo->findOneByStatus(OfflinerModel::STATUS_AWAITING);
            if(isset($task)){
                $script = $this->generateCrawlScript($task,"completed_tasks/".$task->getId());
                exec("node -e \"".$script."\"");
                exec("cd completed_tasks/ && zip ".$task->getId().".zip -r ".$task->getId()." && mv -f ".$task->getId().".zip /home/istrelnikov/offliner_uploads/".$task->getId().".zip");
                $task->setStatus(OfflinerModel::STATUS_READY);
                $task->setReady(true);
                $this->dm->persist($task);
                $this->dm->flush();
            }
            $this->unregProcess();
        }
        return  true;
    }
    public function regProcess(){
        $success = false;
        $this->dm->beginTransaction();
        $query = $this->dm->createQuery('SELECT count(p) FROM ThumbtackOfflinerBundle:Process p');
        if(intval($query->getSingleScalarResult()) < $this->maxProcessCount){
            $pr = new Process();
            $this->dm->persist($pr);
            $this->dm->flush();
            $this->dm->commit();
            $this->process = $pr;
            $success = true;
        }else{
            $this->dm->rollback();
        }
         return $success;
    }
    public function unregProcess(){ //TODO: think about exceptions!
        if($this->process){
            $this->dm->remove($this->process);
            $this->dm->flush();
            $this->dm->clear();
        }
    }
    /**
     * @param Task $task
     * @param string $savePath
     * @return string
     */
    private function generateCrawlScript($task,$savePath){
        $res = "
        var PhantomCrawl = require('./vendor/xplk/phantomCrawl/src/PhantomCrawl');
        var urls = [];
        urls.push('".$task->getUrl()."');
        var p = new PhantomCrawl({
            urls:urls,
            nbThreads:4,
            crawlerPerThread:4,
            maxDepth:".$task->getMaxDepth().",
            base:'".$savePath."',
            pageTransform:[". ($task->getClearScripts()?"'cleanJs', ":"") ."'cleanInlineCss', 'absoluteUrls', 'canvas', 'inputs', 'charset', 'white'],
            urlFilters: [".($task->getOnlyDomain()?"'domain', ":"")."'level', 'crash']
        });
        ";
        return $res;
    }
}