<?php
/**
 * Created by JetBrains PhpStorm.
 * User: istrelnikov
 * Date: 9/20/13
 * Time: 8:42 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Application\Model;
//TODO: error codes/messages
class PageSaverModel {
    //----constants
    const STATUS_AWAITING = 1;
    const STATUS_PROGRESS = 2;
    const STATUS_READY = 3;

    private $dao;
    private $systemDao;
    function __construct(){
        $this->dao = new PageSaverDAO();
        $this->systemDao = new IndexerDAO(); //Link with index processes
    }
    public function runQuerySave(){
        $pr_id = $this->systemDao->regProcess(IndexerModel::PROCESS_COUNT);
        if($pr_id){
            $task = $this->dao->getQueryTask();
            if(isset($task)){
                $script = $this->generateCrawlScript($task,"completed_tasks/".$task->getId());
                //exec("node -e \"".$script."\"");
                exec("cd completed_tasks/ && zip ".$task->getId().".zip -r ".$task->getId()." && mv -f ".$task->getId().".zip ../public/uploads/".$task->getId().".zip");
                $this->dao->setTaskStatus($task->getId(),PageSaverModel::STATUS_READY);
            }
        }
        $this->systemDao->unregProcess($pr_id);
        return  true;
    }
    function addTaskToQuery($url,$depth,$scripts,$domain,$email){
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

    /**
     * @param SaveTaskEntity $task
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
            pageTransform:[". ($task->isClearScripts()?"'cleanJs', ":"") ."'cleanInlineCss', 'absoluteUrls', 'canvas', 'inputs', 'charset', 'white'],
            urlFilters: [".($task->isOnlyDomain()?"'domain', ":"")."'level', 'crash']
        });
        ";
        return $res;
    }
}