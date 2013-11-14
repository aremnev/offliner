<?php

namespace Thumbtack\OfflinerBundle\Models;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Config\Definition\Exception\Exception;
use Thumbtack\AppBundle\Entity\Process;
use Thumbtack\OfflinerBundle\Entity\Task;

// Review: move to top --Resolved
require_once(__DIR__ . '/../Misc/normilize_url.php');

class OfflinerProcessor {
    const STATUS_AWAITING = 'in queue';
    const STATUS_PROGRESS = 'in progress'; //REVU: overhead ????
    const STATUS_READY = 'Ready';
    /** @var EntityManager */
    private $dm;
    /** @var Registry */
    private $doctrine;
    /** @var EntityRepository */
    private $tasksRepo;
    /** @var EntityRepository */
    private $serviceRepo;

    private $maxProcessCount;
    /** @var Process */
    private $process;

    private $uploadPath;


    /**
     * @param $doctrine
     * @param integer $mpc
     * @param string $uploadPath
     */
    function __construct($doctrine, $mpc, $uploadPath) {
        $this->dm = $doctrine->getManager();
        $this->doctrine = $doctrine;
        $this->tasksRepo = $this->dm->getRepository('ThumbtackOfflinerBundle:Task');
        $this->serviceRepo = $this->dm->getRepository('ThumbtackAppBundle:Process');
        $this->maxProcessCount = $mpc;
        $this->uploadPath = $uploadPath;
        $this->process = null;
        error_reporting(E_ERROR | E_WARNING | E_PARSE); // Review: use error_reporting(-1); (E_ALL)
    }

    public function runQueueTask() {
        $result = 'too much processes';
        if ($this->regProcess()) {
            $result = $this->process . ': failed';
            /** @var Task $task */
            $task = $this->tasksRepo->findOneByStatus(OfflinerProcessor::STATUS_AWAITING); //Review: use sql index
            if (!empty($task)) {
                $result = $this->process . ': success';
                $host = parse_url($task->getUrl(), PHP_URL_HOST); // Review: use PHP_URL_HOST --Resolved
                $task->setStatus(OfflinerProcessor::STATUS_PROGRESS);
                $this->dm->persist($task);
                $this->dm->flush();
                $script = $this->generateCrawlScript($task, "completed_tasks/" . $task->getId());
                exec("node -e \"" . $script . "\"");
                exec("cd completed_tasks/ && zip " . $task->getId() . ".zip -r " . $task->getId() .
                    " && mv -f " . $task->getId() . ".zip " . $this->uploadPath . $task->getId() . $host . ".zip");
                $task->setStatus(OfflinerProcessor::STATUS_READY);
                $task->setFilename($task->getId() . $host . ".zip");
                $task->setReady(true);
                $this->dm->persist($task);
                $this->dm->flush();
            }
            $this->unregProcess();
        }
        return $result . "\r\n"; // Review: ??? change return and "if" logic --Resolved
    }

    public function regProcess() {
        try {
            $this->dm->beginTransaction();
            $query = $this->dm->createQuery('SELECT count(p) FROM ThumbtackAppBundle:Process p');
            if (intval($query->getSingleScalarResult()) < $this->maxProcessCount) {
                $pr = new Process();
                $this->dm->persist($pr);
                $this->dm->flush();
                $this->dm->commit();
                $this->process = $pr;
                return true; // Review: return true or false directly --Resolved
            }
            return false;
        } catch (Exception $e) {
            $this->dm->rollback();
            return false;
        }
    }

    public function unregProcess() {
        if (!empty($this->process)) { // Review: isset and make this field null --Resolved
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
    private function generateCrawlScript($task, $savePath) {
        $res = "
        var PhantomCrawl = require('./vendor/xplk/phantomCrawl/src/PhantomCrawl');
        var urls = [];
        urls.push('" . $task->getUrl() . "');
        var p = new PhantomCrawl({
            urls:urls,
            nbThreads:4,
            crawlerPerThread:4,
            maxDepth:" . $task->getMaxDepth() . ",
            base:'" . $savePath . "',
            pageTransform:[" . ($task->getClearScripts() ? "'cleanJs', " : "") . "'cleanInlineCss',
                            'absoluteUrls', 'canvas', 'inputs', 'charset', 'white'],
            urlFilters: [" . ($task->getOnlyDomain() ? "'domain', " : "") . "'level', 'crash']
        });
        ";
        return $res;
    }
}