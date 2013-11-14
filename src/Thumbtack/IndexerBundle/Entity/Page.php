<?php

namespace Thumbtack\IndexerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Thumbtack\IndexerBundle\Models\IndexerProcessor;
use Thumbtack\AppBundle\Entity\User;

/**
 * task
 *
 * @ORM\Table(name="indexer_pages")
 * @ORM\Entity
 */
class Page implements \JsonSerializable {
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Domain
     *
     * @ORM\ManyToOne(targetEntity="Domain", inversedBy="pages")
     * @ORM\JoinColumn(name="domain_id", referencedColumnName="id")
     */

    protected $domain;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=1000)
     */

    protected $url = '';
    /**
     * @var string
     *
     * @ORM\Column(name="hash_url", type="string", length=100)
     */
    protected $hashUrl = '';
    /**
     * @var string
     *
     * @ORM\Column(name="title", type="text", length=65531)
     */
    protected $title = '';
    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", length=16777000 )
     */
    protected $content = '';
    /**
     * @var string
     *
     * @ORM\Column(name="html", type="text", length=16777000 )
     */
    protected $html = '';
    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=20)
     */
    protected $status = '';
    /**
     * @var bool
     *
     * @ORM\Column(name="ready", type="boolean")
     */
    protected $ready; //REVU: $status as ENUM , delete ready

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    protected $date;

    public function __construct($url) {
        $this->status = IndexerProcessor::STATUS_AWAITING;
        $this->ready = false;
        $this->setUrl($url);
        $this->date = new \DateTime();
    }

    public function __toString() {
        return json_encode($this->jsonSerialize());
    }
    public function jsonSerialize() {
        return array(
            "id" => $this->id,
            "url" => $this->url,
            'hash_url' => $this->hashUrl,
            "date" => $this->date,
            "title" => $this->title
        );
    }

    /**
     * @return IndexerUser
     */
    public function getUser() {
        return $this->domain->getUser();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return Page
     */
    public function setUrl($url) {
        $this->url = $url;
        $this->hashUrl = md5($url);

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl() {
        return $this->url;
    }
    /**
     * Get hashUrl
     *
     * @return string
     */
    public function getHashUrl() {
        return $this->hashUrl;
    }
    /**
     * Set status
     *
     * @param string $status
     * @return Page
     */
    public function setStatus($status) {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * Set ready
     *
     * @param boolean $ready
     * @return Page
     */
    public function setReady($ready) {
        $this->ready = $ready;

        return $this;
    }

    /**
     * Get ready
     *
     * @return boolean
     */
    public function getReady() {
        return $this->ready;
    }

    /**
     * Set title
     *
     * @param $title
     * @return Page
     */
    public function setTitle($title) {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * Set content
     *
     * @param $content
     * @return Page
     */
    public function setContent($content) {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent() {
        return $this->content;
    }

    /**
     * @param string $html
     * @return Page
     */
    public function setHtml($html) {
        $this->html = $html;

        return $this;
    }

    /**
     * @return string
     */
    public function getHtml() {
        return $this->html;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Page
     */
    public function setDate($date) {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate() {
        return $this->date;
    }

    /**
     * @param Domain $domain
     * @return Page
     */
    public function setDomain($domain) {
        $this->domain = $domain;

        return $this;
    }

    /**
     * @return Domain
     */
    public function getDomain() {
        return $this->domain;
    }
}