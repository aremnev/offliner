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
        error_reporting(E_ERROR | E_WARNING | E_PARSE); // Review: use error_reporting(-1); (E_ALL)
    }
    public function runQueueTask(){
        if($this->regProcess()){
            /**
             * @var Task $task;
             */
            $task = $this->tasksRepo->findOneByStatus(ServiceProcessor::STATUS_AWAITING);
            if(isset($task)){
                $parsed = parse_url($task->getUrl()); // Review: use PHP_URL_HOST
                $host = $parsed['host'];
                $task->setStatus(ServiceProcessor::STATUS_PROGRESS);
                $this->dm->persist($task);
                $this->dm->flush();
                $script = $this->generateCrawlScript($task,"completed_tasks/".$task->getId());
                exec("node -e \"".$script."\"");
                exec("cd completed_tasks/ && zip ".$task->getId().".zip -r ".$task->getId()." && mv -f ".$task->getId().".zip ".$this->uploadPath.$task->getId().$host.".zip");
                $task->setStatus(ServiceProcessor::STATUS_READY);
                $task->setReady(true); // Review: unnedeed
                $this->dm->persist($task);
                $this->dm->flush();
            }
            $this->unregProcess();
        }
        return  true; // Review: ??? change return and "if" logic
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
                // Review: move to top
                require_once(__DIR__.'/../Misc/normilize_url.php');

                $parsed_page = Crawler::getPage($page->getUrl());
                if($parsed_page){ // Review: use empty()
                    foreach($parsed_page['links'] as $link){
                        $link = normilize_url($link);
                        $existed = $this->pagesRepo->findOneByHashUrl(md5($link));
                        if( $this->checkDomain($link, $domain->getHost()) && !$existed ){
                            $newPage = new Page($link);
                            $newPage->setDomain($domain);
                            $this->dm->persist($newPage);
                            $this->dm->flush();
                        }
                    }
                    $page->setReady(true); // Review: remove unnecessary
                    $page->setStatus(ServiceProcessor::STATUS_READY);
                    $page->setContent($parsed_page['plain']);
                    $page->setHtml($parsed_page['html']);
                    $page->setTitle($parsed_page['title']);
                    $this->dm->persist($page);
                }else{
                    $this->dm->remove($page); // Review: ???
                }
                $this->dm->flush();
            }
            $this->unregProcess();
        }
        return  true; // Review: ??? change return and "if" logic
    }
    public function runStatUpdate(){
            /**
             * @var Domain $domain;
             */
            $query = $this->dm->createQuery(
                'SELECT d
                 FROM ThumbtackOfflinerBundle:Domain d
                 WHERE d.refreshDate < :date
                 AND d.status != :ready'
            )->setMaxResults(1)->setParameter('ready', ServiceProcessor::STATUS_READY);
            $date = new \DateTime();
            $date->sub(new \DateInterval('PT10M'));
            $query->setParameter('date',$date);
            $domain = $query->getResult()[0];
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
                }
                $domain->setStatistics(json_encode($result));
                $domain->setRefreshDate(new \DateTime('now'));
                $this->dm->persist($domain);
                $this->dm->flush();
            }else{
                $query = $this->dm->createQuery(
                    'SELECT d
                     FROM ThumbtackOfflinerBundle:Domain d
                     WHERE d.refreshDate < :date
                     AND d.status = :ready'
                )->setMaxResults(1)->setParameter('ready', ServiceProcessor::STATUS_READY);
                $date = new \DateTime();
                $date->modify('-1 day');;
                $query->setParameter('date',$date);
                $domain = $query->getResult()[0];
                if($domain){
                    $domain->setStatus(ServiceProcessor::STATUS_PROGRESS);
                    foreach($domain->getPages() as $page){
                        $page->setStatus(ServiceProcessor::STATUS_AWAITING);
                        $this->dm->persist($page);
                    }
                    $this->dm->persist($domain);
                    $this->dm->flush();
                }
            }
        return true;
    }

    public function regProcess(){
        $success = false; // Review: remove, use return true or false directly
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
        if($this->process){ // Review: isset and make this field null
            $this->dm->remove($this->process);
            $this->dm->flush();
            $this->dm->clear();
        }
    }

    private function checkDomain($url,$domainHost){
        $parsed = parse_url($url); // Review: use PHP_URL_HOST
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