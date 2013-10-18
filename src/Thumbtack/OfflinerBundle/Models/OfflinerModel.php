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
use Thumbtack\OfflinerBundle\Entity\Process;
use Thumbtack\OfflinerBundle\Entity\Task;

class OfflinerModel {
    //----constants
    const STATUS_AWAITING = 'in query';
    const STATUS_PROGRESS = 'in progress';
    const STATUS_READY = 'Ready';

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
     * @param EntityManager $dm
     * @param integer $mpc
     */
    function __construct($dm,$mpc){
        $this->dm = $dm;
        $this->tasksRepo = $dm->getRepository('ThumbtackOfflinerBundle:Task');
        $this->serviceRepo = $dm->getRepository('ThumbtackOfflinerBundle:Process');
        $this->maxProcessCount = $mpc;
    }
    public function runQuerySave(){
        if($this->regProcess()){
            /**
             * @var Task $task;
             */
            $task = $this->tasksRepo->findOneByStatus(OfflinerModel::STATUS_AWAITING);
            if(isset($task)){
                $script = $this->generateCrawlScript($task,"completed_tasks/".$task->getId());
                exec("node -e \"".$script."\"");
                exec("cd completed_tasks/ && zip ".$task->getId().".zip -r ".$task->getId()." && mv -f ".$task->getId().".zip ../public/uploads/".$task->getId().".zip");
                $task->setStatus(PageSaverModel::STATUS_READY);
                $this->dm->persist($task);
                $this->dm->flush();
            }
            $this->unregProcess();
        }
        return  true;
    }
    function addTaskToQuery($data){
        $entity = new Task($data);
         return $entity;
        $url = $this->prepareURL($url);
        //TODO: implement UserEntity, create TaskEntity
        $uid = $email;
        return $this->dao->addTask($url,$depth,$scripts,$domain,$uid);
    }

    function getTaskStatusByURL($url){
        $url = $this->prepareURL($url);
        $task = $this->dao->getTaskByUrl($url);
        if(!is_null($task)){
            $ret = array();
            $ret['id'] = $task->getId();
            $ret['status'] = $task->getStatus();
            $ret['date'] = $task->getDate();
            $ret['depth'] = $task->getMaxDepth();
            $ret['url'] = $task->getUrl();
            $ret['cl_scripts'] = $task->isClearScripts();
            $ret['only_domain'] = $task->isOnlyDomain();
            return json_encode($ret);
        }else{
            return null;
        }
    }
    function getTaskStatusByUser($email){
        $task = $this->dao->getTaskByUserId($email);
        if(!is_null($task)){
            $ret = array();
            $ret['id'] = $task->getId();
            $ret['status'] = $task->getStatus();
            $ret['date'] = $task->getDate();
            $ret['depth'] = $task->getMaxDepth();
            $ret['url'] = $task->getUrl();
            $ret['cl_scripts'] = $task->isClearScripts();
            $ret['only_domain'] = $task->isOnlyDomain();
            return json_encode($ret);
        }else{
            return null;
        }
    }
    public function prepareURL($url){
        $url = str_replace(array('\\"','\\\'','\'','"'),'',$url);
        $url = reset(explode('#',$url));
        if(substr($url, 0, 2) === '//'){
            $url = 'http:'.$url;
        }
        $url= str_replace('www.','',$url);
        $url = preg_replace('#(?:http(s)?://)?(.+)#', 'http\1://\2', $url);
        return $url;
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