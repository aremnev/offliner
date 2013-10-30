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
use Thumbtack\OfflinerBundle\Entity\Page;
use Thumbtack\OfflinerBundle\Entity\Process;
use Thumbtack\OfflinerBundle\Entity\Task;
use Thumbtack\OfflinerBundle\Entity\User;

class ServiceProcessor {
    const STATUS_AWAITING = 'in queue';
    const STATUS_PROGRESS = 'in progress';
    const STATUS_READY = 'Ready';
    /**
     * @var EntityManager
     */
    private  $dm;
    private  $doctrine;
    /**
     * @var EntityRepository
     */
    private $tasksRepo;
    /**
     * @var EntityRepository
     */
    private $serviceRepo;
    /**
     * @var EntityRepository
     */
    private $pagesRepo;
    private $maxProcessCount;
    /**
     * @var Process
     */
    private $process;
    private $index;

    /**
     * @param $doctrine
     * @param integer $mpc
     * @param $secure
     * @param $index
     */
    function __construct($doctrine,$mpc,$index){
        $this->dm = $doctrine->getManager();
        $this->doctrine = $doctrine;
        $this->tasksRepo = $this->dm->getRepository('ThumbtackOfflinerBundle:Task');
        $this->serviceRepo = $this->dm->getRepository('ThumbtackOfflinerBundle:Process');
        $this->pagesRepo = $this->dm->getRepository('ThumbtackOfflinerBundle:Page');
        $this->maxProcessCount = $mpc;
        $this->index = $index;
    }
    public function runQueueTask(){
        if($this->regProcess()){
            /**
             * @var Task $task;
             */
            $task = $this->tasksRepo->findOneByStatus(ServiceProcessor::STATUS_AWAITING);
            if(isset($task)){
                $task->setStatus(ServiceProcessor::STATUS_PROGRESS);
                $this->dm->persist($task);
                $this->dm->flush();
                $script = $this->generateCrawlScript($task,"completed_tasks/".$task->getId());
                exec("node -e \"".$script."\"");
                exec("cd completed_tasks/ && zip ".$task->getId().".zip -r ".$task->getId()." && mv -f ".$task->getId().".zip /home/istrelnikov/offliner_uploads/".$task->getId().".zip");
                $task->setStatus(ServiceProcessor::STATUS_READY);
                $task->setReady(true);
                $this->dm->persist($task);
                $this->dm->flush();
            }
            $this->unregProcess();
        }
        return  true;
    }
    public function runIndexing(){
        if($this->regProcess()){
            /**
             * @var Page $page;
             */
            $page = $this->pagesRepo->findOneByStatus(ServiceProcessor::STATUS_AWAITING);
            if(isset($page)){
                $page->setStatus(ServiceProcessor::STATUS_PROGRESS);
                $this->dm->persist($page);
                $this->dm->flush();

                $parsed_page = Crawler::getPage($page->getUrl());
                if($parsed_page){
                    $user = $page->getUser();
                    $indexer = new IndexerModel($user,$this->index,$this->doctrine);
                    foreach($parsed_page['links'] as $link){
                        $indexer->addToQuery($link);
                    }
                    $page->setReady(true);
                    $page->setStatus(ServiceProcessor::STATUS_READY);
                    $page->setContent($parsed_page['plain']);
                    $page->setHtml($parsed_page['html']);
                    $page->setTitle($parsed_page['title']);
                    $this->dm->persist($page);
                }else{
                    $this->dm->remove($page);
                }
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