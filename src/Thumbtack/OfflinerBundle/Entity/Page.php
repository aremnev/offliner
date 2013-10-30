<?php

namespace Thumbtack\OfflinerBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints\DateTime;
use Thumbtack\OfflinerBundle\Models\ServiceProcessor;

/**
 * task
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Page implements \JsonSerializable{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="pages")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;
    /**
     * @var string
     *
     * @ORM\Column(name="domain", type="string", length=1000)
     */
    protected $domain = '';

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=65531)
     */
    protected $url = '';
    /**
     * @var string
     *
     * @ORM\Column(name="hash_url", type="string", length=100)
     */
    protected $hash_url = '';
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
    protected $ready;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    protected $date;
    public function __construct(){
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
     * @return Task
     */
    public function setUrl($url) {
        $this->url = $url;
        $this->hash_url = md5($url);
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
     * Set status
     *
     * @param string $status
     * @return Task
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
     * @return Task
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
     * @return Task
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
     * @return Task
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
     */
    public function setHtml($html) {
        $this->html = $html;
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
     * @return Task
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
     * @param string $domain
     */
    public function setDomain($domain) {
        $this->domain = $domain;
    }

    /**
     * @return string
     */
    public function getDomain() {
        return $this->domain;
    }
    /**
     * Set user
     *
     * @param \Thumbtack\OfflinerBundle\Entity\User $user
     * @return Task
     */
    public function setUser(\Thumbtack\OfflinerBundle\Entity\User $user = null) {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Thumbtack\OfflinerBundle\Entity\User
     */
    public function getUser() {
        return $this->user;
    }


    public function __toString() {
        return json_encode($this->jsonSerialize);
    }
    public function jsonSerialize() {
        return array("id"=>$this->id,"url"=>$this->url,'hash_url'=>$this->hash_url,"date"=>$this->date,"title"=>$this->title);
    }}