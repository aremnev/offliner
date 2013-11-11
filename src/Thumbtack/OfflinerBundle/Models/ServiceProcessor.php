<?php

namespace Thumbtack\OfflinerBundle\Models;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Config\Definition\Exception\Exception;
use Thumbtack\OfflinerBundle\Entity\Domain;
use Thumbtack\OfflinerBundle\Entity\Page;
use Thumbtack\OfflinerBundle\Entity\Process;
use Thumbtack\OfflinerBundle\Entity\Task;

class ServiceProcessor {
    const STATUS_AWAITING = 'in queue';
    const STATUS_PROGRESS = 'in progress';
    const STATUS_READY = 'Ready';
    /**
     * @var EntityManager
     */
    private  $dm;
    /**
     * @var Registry
     */
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
    /**
     * @var EntityRepository
     */
    private $domainsRepo;
    private $maxProcessCount;
    /**
     * @var Process
     */
    private $process;

    private $uploadPath;


    /**
     * @param $doctrine
     * @param integer $mpc
     * @param string $uploadPath
     */
    function __construct($doctrine,$mpc,$uploadPath){
        $this->dm = $doctrine->getManager();
        $this->doctrine = $doctrine;
        $this->tasksRepo = $this->dm->getRepository('ThumbtackOfflinerBundle:Task');
        $this->serviceRepo = $this->dm->getRepository('ThumbtackOfflinerBundle:Process');
        $this->pagesRepo = $this->dm->getRepository('ThumbtackOfflinerBundle:Page');
        $this->domainsRepo = $this->dm->getRepository('ThumbtackOfflinerBundle:Domain');
        $this->maxProcessCount = $mpc;
        $this->uploadPath = $uploadPath;
    }
    public function runQueueTask(){
        if($this->regProcess()){
            /**
             * @var Task $task;
             */
            $task = $this->tasksRepo->findOneByStatus(ServiceProcessor::STATUS_AWAITING);
            if(isset($task)){
                $parsed = parse_url($task->getUrl());
                $host = $parsed['host'];
                $task->setStatus(ServiceProcessor::STATUS_PROGRESS);
                $this->dm->persist($task);
                $this->dm->flush();
                $script = $this->generateCrawlScript($task,"completed_tasks/".$task->getId());
                exec("node -e \"".$script."\"");
                exec("cd completed_tasks/ && zip ".$task->getId().".zip -r ".$task->getId()." && mv -f ".$task->getId().".zip ".$this->uploadPath.$task->getId().$host.".zip");
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
                $domain = $page->getDomain();
                $page->setStatus(ServiceProcessor::STATUS_PROGRESS);
                $this->dm->persist($page);
                $this->dm->flush();
                require_once(__DIR__.'/../Misc/normilize_url.php');
                $parsed_page = Crawler::getPage($page->getUrl());
                if($parsed_page){
                    foreach($parsed_page['links'] as $link){
                        $link = normilize_url($link);
                        $existed = $this->pagesRepo->findOneByHashUrl(md5($link));
                        if($this->checkDomain($link,$domain->getHost()) && !$existed){
                            $newPage = new Page($link);
                            $newPage->setDomain($domain);
                            $this->dm->persist($newPage);
                            $this->dm->flush();
                        }
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
    public function runStatUpdate(){
        if($this->regProcess()){
            /**
             * @var Domain $domain;
             */
            $domain = $this->domainsRepo->findOneByStatus(ServiceProcessor::STATUS_AWAITING);
            if(isset($domain)){
                $result = array();
                $query = $this->dm->createQuery(
                   'SELECT count(p)
                    FROM ThumbtackOfflinerBundle:Page p
                    WHERE p.domain = :domain
                    AND p.status = :status'
                )->setParameter('domain', $domain);
                $query->setParameter('status',ServiceProcessor::STATUS_AWAITING);
                $result['await'] = $query->getSingleScalarResult();
                $query->setParameter('status',ServiceProcessor::STATUS_PROGRESS);
                $result['progress'] = $query->getSingleScalarResult();
                $query->setParameter('status',ServiceProcessor::STATUS_READY);
                $result['ready'] = $query->getSingleScalarResult();
                if($result['ready'] != 0 && $result['progress'] == 0 && $result['await']==0){
                    $domain->setStatus(ServiceProcessor::STATUS_READY);
                    $result['lastTotal'] = $result['ready'];
                }else{
                    $domain->setStatus(ServiceProcessor::STATUS_PROGRESS);
                    $result['lastTotal'] = 15;
                }
                $domain->setStatistics(json_encode($result));
                $this->dm->persist($domain);
                $this->dm->flush();
            }
            $this->unregProcess();
        }
        return true;
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
    public function unregProcess(){
        if($this->process){
            $this->dm->remove($this->process);
            $this->dm->flush();
            $this->dm->clear();
        }
    }
    private function checkDomain($url,$domainHost){
        $parsed = parse_url($url);
        return $parsed['host'] == $domainHost;
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