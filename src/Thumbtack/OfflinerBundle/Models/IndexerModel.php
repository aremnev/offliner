<?php
namespace Thumbtack\OfflinerBundle\Models;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Elastica\Query\Bool;
use Elastica\Query\Terms;
use Elastica\Query\Text;
use Elastica\Query;
use Elastica\Result;
use Elastica\ResultSet;
use Elastica\Type;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use FOS\ElasticaBundle\HybridResult;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Validator\Constraints\DateTime;
use Thumbtack\OfflinerBundle\Entity\Page;
use Thumbtack\OfflinerBundle\Entity\Process;
use Thumbtack\OfflinerBundle\Entity\Task;
use Thumbtack\OfflinerBundle\Entity\User;

class IndexerModel {
    private $user;
    private $index;
    /* @var EntityRepository $repo*/
    private $repo;
    private $dm;

    public function __construct(User $user,Type $index,EntityManager $doctrine){
        $this->user = $user;
        $this->index = $index;
        $this->dm = $doctrine;
        $this->repo = $doctrine->getRepository('ThumbtackOfflinerBundle:Page');
    }

    public function find($string){
        $boolQuery = new Bool();
        $userQuery = new Terms();
        $userQuery->setTerms('user',array($this->user->getId()));
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
            $output[$i]['page'] = $this->repo->findOneById($result->getId());
            $output[$i]['highlights']= $result->getHighlights();
            $output[$i]['$maxScore']= $maxScore;
            $output[$i++]['score']= intval($result->getScore()/$maxScore*100);

        }
        return $output;
    }

    function addToQuery($url){
        $url = $this->prepareURL($url);
        $page = new Page();
        $page->setStatus(ServiceProcessor::STATUS_AWAITING);
        $page->setReady(false);
        $page->setUrl($url);
        $page->setUser($this->user);
        $page->setDate(new \DateTime());
        $this->dm->persist($page);
        $this->dm->flush();
        return true;
    }

    function getPageStatus($url){
        $url = $this->prepareURL($url);
        return $this->repo->findOneBy(array('hash_url'=>md5($url)));
    }
    /*function getDomainInfo($url){
        $url = $this->prepareURL($url);
        $parsed_url = parse_url($url);
        $domain = $parsed_url['host'];
        $result = $this->dao->getDomainInfo($domain);
        if(!is_null($result)){
            return json_encode($result);
        }else{
            return null;
        }
    }*/
    public function prepareURL($url){
        $url = rtrim($url,'/');
        $url = str_replace(array('\\"','\\\'','\'','"'),'',$url);
        $url = reset(explode('#',$url));
        if(substr($url, 0, 2) === '//'){
            $url = 'http:'.$url;
        }
        $url= str_replace('www.','',$url);
        $url = preg_replace('#(?:http(s)?://)?(.+)#', 'http\1://\2', $url);
        return $url;
    }
}