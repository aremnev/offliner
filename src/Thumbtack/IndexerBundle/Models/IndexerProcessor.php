<?php

namespace Thumbtack\IndexerBundle\Models;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Config\Definition\Exception\Exception;
use Thumbtack\IndexerBundle\Entity\Domain;
use Thumbtack\IndexerBundle\Entity\Page;
use Thumbtack\AppBundle\Entity\Process;

// Review: move to top --Resolved
require_once(__DIR__ . '/../Misc/normilize_url.php');

class IndexerProcessor {
    const STATUS_AWAITING = 'in queue';
    const STATUS_PROGRESS = 'in progress'; //REVU: overhead ????
    const STATUS_READY = 'Ready';
    /** @var EntityManager */
    private $dm;
    /** @var Registry */
    private $doctrine;
    /** @var EntityRepository */
    private $serviceRepo;
    /** @var EntityRepository */
    private $pagesRepo;
    /** @var EntityRepository */
    private $domainsRepo;

    private $maxProcessCount;
    /** @var Process */
    private $process;


    /**
     * @param $doctrine
     * @param integer $mpc
     */
    function __construct($doctrine, $mpc) {
        $this->dm = $doctrine->getManager();
        $this->doctrine = $doctrine;
        $this->serviceRepo = $this->dm->getRepository('ThumbtackAppBundle:Process'); //TODO: move Processes control in Commands
        $this->pagesRepo = $this->dm->getRepository('ThumbtackIndexerBundle:Page');
        $this->domainsRepo = $this->dm->getRepository('ThumbtackIndexerBundle:Domain');
        $this->maxProcessCount = $mpc;
        $this->process = null;
        error_reporting(E_ERROR | E_WARNING | E_PARSE);
    }
    public function runIndexing() {
        $result = 'too much processes';
        if ($this->regProcess()) {
            $result = $this->process . ': failed';
            /** @var Page $page  */
            $page = $this->pagesRepo->findOneByStatus(IndexerProcessor::STATUS_AWAITING);
            if (!empty($page)) {
                $domain = $page->getDomain();
                $page->setStatus(IndexerProcessor::STATUS_PROGRESS);
                $this->dm->persist($page);
                $this->dm->flush();
                $parsed_page = Crawler::getPage($page->getUrl());
                if ($parsed_page) { // Review: use empty()
                    $result = $this->process . ': success';
                    foreach ($parsed_page['links'] as $link) {
                        $link = normilize_url($link);
                        $existed = $this->pagesRepo->findOneByHashUrl(md5($link));
                        if ($this->checkDomain($link, $domain->getHost()) && !$existed) {
                            $newPage = new Page($link);
                            $newPage->setDomain($domain);
                            $this->dm->persist($newPage);
                            $this->dm->flush();
                        }
                    }
                    $page->setReady(true); // Review: remove unnecessary
                    $page->setStatus(IndexerProcessor::STATUS_READY);
                    $page->setContent($parsed_page['plain']);
                    $page->setHtml($parsed_page['html']);
                    $page->setTitle($parsed_page['title']);
                    $this->dm->persist($page);
                } else {
                    $this->dm->remove($page); // Review: ??? --Deleting if crawl fail
                }
                $this->dm->flush();
            }
            $this->unregProcess();
        }
        return $result . "\r\n"; // Review: ??? change return and "if" logic --Resolved
    }

    public function runStatUpdate() {
        $resp = 'statUpdate failed';
        /** @var Domain $domain  */
        $query = $this->dm->createQuery('SELECT d
                 FROM ThumbtackIndexerBundle:Domain d
                 WHERE d.refreshDate < :date
                 AND d.status != :ready')->setMaxResults(1)->setParameter('ready', IndexerProcessor::STATUS_READY);
        $date = new \DateTime();
        $date->sub(new \DateInterval('PT10M'));
        $query->setParameter('date', $date);
        $domain = $query->getResult()[0];
        if (!empty($domain)) {
            $resp = 'statUpdate success';
            $result = array();
            $query = $this->dm->createQuery('SELECT count(p)
                    FROM ThumbtackIndexerBundle:Page p
                    WHERE p.domain = :domain
                    AND p.status = :status')->setParameter('domain', $domain);
            $query->setParameter('status', IndexerProcessor::STATUS_AWAITING);
            $result['await'] = $query->getSingleScalarResult();
            $query->setParameter('status', IndexerProcessor::STATUS_PROGRESS);
            $result['progress'] = $query->getSingleScalarResult();
            $query->setParameter('status', IndexerProcessor::STATUS_READY);
            $result['ready'] = $query->getSingleScalarResult();
            if ($result['ready'] != 0 && $result['progress'] == 0 && $result['await'] == 0) {
                $domain->setStatus(IndexerProcessor::STATUS_READY);
                $result['lastTotal'] = $result['ready'];
            } else {
                $domain->setStatus(IndexerProcessor::STATUS_PROGRESS);
            }
            $domain->setStatistics(json_encode($result));
            $domain->setRefreshDate(new \DateTime('now'));
            $this->dm->persist($domain);
            $this->dm->flush();
        } else {
            $query = $this->dm->createQuery('SELECT d
                     FROM ThumbtackIndexerBundle:Domain d
                     WHERE d.refreshDate < :date
                     AND d.status = :ready')->setMaxResults(1)->setParameter('ready', IndexerProcessor::STATUS_READY);
            $date = new \DateTime();
            $date->modify('-1 day');;
            $query->setParameter('date', $date);
            $domain = $query->getResult()[0];
            if ($domain) {
                $resp = 'statUpdate success, reindex ' . $domain->getUrl();
                $domain->setStatus(IndexerProcessor::STATUS_PROGRESS);
                foreach ($domain->getPages() as $page) {
                    $page->setStatus(IndexerProcessor::STATUS_AWAITING);
                    $this->dm->persist($page);
                }
                $this->dm->persist($domain);
                $this->dm->flush();
            }
        }
        return $resp . "\r\n";
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

    private function checkDomain($url, $domainHost) {
        // Review: use PHP_URL_HOST --Resolved
        return parse_url($url, PHP_URL_HOST) == $domainHost;
    }

}