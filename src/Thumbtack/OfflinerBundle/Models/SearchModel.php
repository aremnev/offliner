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
use Thumbtack\OfflinerBundle\Entity\Process;
use Thumbtack\OfflinerBundle\Entity\Task;
use Thumbtack\OfflinerBundle\Entity\User;

class SearchModel {
    private $user;
    private $finder;
    /* @var EntityRepository $repo*/
    private $repo;

    public function __construct(User $user,Type $finder,$repo){
        $this->user = $user;
        $this->finder = $finder;
        $this->repo = $repo;
    }

    public function find($string){
        $boolQuery = new Bool();
        $userQuery = new Terms();
        $userQuery->setTerms('user',array($this->user->getId()));
        $boolQuery->addMust($userQuery);
        $boolQuery->setMinimumNumberShouldMatch(1);
        $contentQuery = new Text();
        $contentQuery->setFieldQuery('content',$string);
        $contentQuery->setFieldParam('content', 'analyzer', 'my_analyzer');
        $boolQuery->addShould($contentQuery);
        $titleQuery = new Text();
        $titleQuery->setFieldQuery('title',$string);
        $titleQuery->setFieldParam('title', 'analyzer', 'my_analyzer');
        $boolQuery->addShould($titleQuery);
        $query = new Query();
        $query->setQuery($boolQuery);
        $query->setHighlight(array('order'=>'score','fields'=>array('content'=>array('fragment_size'=>120,'number_of_fragments'=>5))));
        /** @var ResultSet $results */
        $results = $this->finder->search($query);
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
}