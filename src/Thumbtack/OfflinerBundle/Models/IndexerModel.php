<?php
namespace Thumbtack\OfflinerBundle\Models;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Elastica\Query\Bool;
use Elastica\Query\Terms;
use Elastica\Query;
use Elastica\Result;
use Elastica\ResultSet;
use Elastica\Type;
use Symfony\Component\Config\Definition\Exception\Exception;
use Thumbtack\OfflinerBundle\Entity\Domain;
use Thumbtack\OfflinerBundle\Entity\Page;
use Thumbtack\OfflinerBundle\Entity\User;

class IndexerModel {
    private $user;
    private $index;
    /* @var EntityRepository $pageRepo*/
    private $pageRepo;
    /* @var EntityRepository $domainRepo*/
    private $domainRepo;
    private $dm;

    public function __construct(User $user,Type $index,EntityManager $doctrine){
        $this->user = $user;
        $this->index = $index;
        $this->dm = $doctrine;
        $this->pageRepo = $doctrine->getRepository('ThumbtackOfflinerBundle:Page');
        $this->domainRepo = $doctrine->getRepository('ThumbtackOfflinerBundle:Domain');
    }

    public function find($string,$domainId){
        $boolQuery = new Bool();
        $userQuery = new Terms();
        $userQuery->setTerms('user',array($this->user->getId()));
        if(isset($domainId)){
            $userQuery->addTerm('domain',array($domainId));
        }
        $boolQuery->addMust($userQuery);
        $boolQuery->setMinimumNumberShouldMatch(1);
        $contentQuery = new Query\FuzzyLikeThis();
        $contentQuery->addFields(array('content','title'));
        $contentQuery->setLikeText($string);
        $contentQuery->setMinSimilarity(0.67);
        $contentQuery->setParam('analyzer','my_analyzer');
        $boolQuery->addShould($contentQuery);
        $query = new Query();
        $query->setQuery($boolQuery);
        $query->setHighlight(array('order'=>'score','fields'=>array('content'=>array('fragment_size'=>120,'number_of_fragments'=>5))));
        /** @var ResultSet $results */
        $results = $this->index->search($query);
        $output = array();
        $i = 0;
        $maxScore = $results->getMaxScore();
        /**@var Result $result */
        foreach($results->getResults() as $result){
            $output[$i]['page'] = $this->pageRepo->findOneById($result->getId());
            $output[$i]['highlights']= $result->getHighlights();
            $output[$i]['maxScore']= $maxScore;
            $output[$i++]['score']= intval($result->getScore()/$maxScore*100);

        }
        return $output;
    }

    function addDomain($json){
        try{
            require_once(__DIR__.'/../Misc/normilize_url.php');
            $data = json_decode($json,true);
            $data['url']= normilize_url($data['url']);
            $data['status'] = ServiceProcessor::STATUS_AWAITING;
            $domain = new Domain($data);
            $domain->setUser($this->user);
            $first_page = new Page($data['url']);
            $first_page->setDomain($domain);
            $this->dm->persist($domain);
            $this->dm->persist($first_page);
            $this->dm->flush();
            return true;
        }catch (Exception $e){
            return false;
        }
    }
    function deleteDomainById($id){ //TODO:related page too!
        $domain = $this->domainRepo->findOneById($id);
        if($domain){
            $this->dm->remove($domain);
            $this->dm->flush();
            return true;
        }else{
            return false;
        }
    }
    function getDomainInfo($id){
        /** @var Domain $domain */
        $domain = $this->domainRepo->findOneById($id);
        return $domain->getStatistics();
    }
}