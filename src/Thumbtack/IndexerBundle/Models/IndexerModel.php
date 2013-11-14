<?php
namespace Thumbtack\IndexerBundle\Models;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Elastica\Query\Bool;
use Elastica\Query\Terms;
use Elastica\Query;
use Elastica\Result;
use Elastica\ResultSet;
use Elastica\Type;
use Symfony\Component\Config\Definition\Exception\Exception;
use Thumbtack\IndexerBundle\Entity\Domain;
use Thumbtack\IndexerBundle\Entity\IndexerUserInterface;
use Thumbtack\IndexerBundle\Entity\Page;

// Review: need up to begin of file --Resolved
require_once(__DIR__ . '/../Misc/normilize_url.php');
class IndexerModel {
    private $user;
    private $index;
    /* @var EntityRepository $pageRepo */
    private $pageRepo;
    /* @var EntityRepository $domainRepo */
    private $domainRepo;
    private $dm;

    public function __construct($user, Type $index, EntityManager $doctrine) {
        if(!($user instanceof IndexerUserInterface)){
            throw new Exception('constructor param User must implements IndexerUserInterface');
        }
        $this->user = $user;
        $this->index = $index;
        $this->dm = $doctrine;
        $this->pageRepo = $doctrine->getRepository('ThumbtackIndexerBundle:Page');
        $this->domainRepo = $doctrine->getRepository('ThumbtackIndexerBundle:Domain');
    }

    public function find($string, $domainId = null) {
        $boolQuery = new Bool();
        $userQuery = new Terms();
        $userQuery->setTerms('user', array($this->user->getIndexerUser()->getId()));
        if (!empty($domainId)) {
            $userQuery->setTerms('domain', array($domainId)); //TODO: check working
        }
        $boolQuery->addMust($userQuery);
        $boolQuery->setMinimumNumberShouldMatch(1);
        $contentQuery = new Query\FuzzyLikeThis();
        $contentQuery->addFields(array('content', 'title'));
        $contentQuery->setLikeText($string);
        $contentQuery->setMinSimilarity(0.67);
        $contentQuery->setParam('analyzer', 'my_analyzer');
        $boolQuery->addShould($contentQuery);
        $query = new Query();
        $query->setQuery($boolQuery);
        $query->setHighlight(array('order' => 'score', 'fields' => array('content' => array('fragment_size' => 120, 'number_of_fragments' => 5))));
        /** @var ResultSet $results */
        $results = $this->index->search($query);
        $output = array();
        $i = 0;
        $maxScore = $results->getMaxScore();
        /**@var Result $result */
        foreach ($results->getResults() as $result) {
            $output[$i]['page'] = $this->pageRepo->findOneById($result->getId());
            $output[$i]['highlights'] = $result->getHighlights();
            $output[$i]['maxScore'] = $maxScore;
            $output[$i++]['score'] = intval($result->getScore() / $maxScore * 100);

        }
        return $output;
    }

    function addDomain($json) {
        try {
            if (empty($json)) { //TODO: add validation
                return false;
            }
            // Review: check on bad value --Resolved in controller
            $data = json_decode($json, true);
            $data['url'] = normilize_url($data['url']);
            $data['status'] = IndexerProcessor::STATUS_AWAITING;
            // Review: Domain doesn't  have params --Resolved
            $domain = new Domain();
            $domain->setUrl($data['url']);
            $domain->setStatus($data['status']);
            $domain->setUser($this->user->getIndexerUser());
            $first_page = new Page($data['url']);
            $first_page->setDomain($domain);
            $this->dm->persist($domain);
            $this->dm->persist($first_page);
            $this->dm->flush();
            return true;
        } catch (Exception $e) {
            //supress&log...
            return false;
        }
    }

    function deleteDomainById($id) { //TODO:check cascade pages persist/remove
        try {
            $domain = $this->domainRepo->findOneById($id);
            if (empty($domain)) {
                return false;
            }
            // Review: 'else' can remove --Resolved
            // What will be If exception is happening? --Resolved
            $this->dm->remove($domain);
            $this->dm->flush();
            return true;
        } catch (Exception $e) {
            //suppress&log
            return false;
        }
    }

    function getDomainInfo($id) { //TODO: create good statistic architecture
        /** @var Domain $domain */
        $domain = $this->domainRepo->findOneById($id);
        return $domain->getStatistics();
    }
}