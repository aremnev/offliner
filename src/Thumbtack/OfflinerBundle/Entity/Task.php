<?php

namespace Thumbtack\OfflinerBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * task
 *
 * @ORM\Table(name="offliner_tasks")
 * @ORM\Entity
 */
class Task implements \JsonSerializable {
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
     * @ORM\ManyToOne(targetEntity="User", inversedBy="tasks")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @var integer
     *
     * @ORM\Column(name="max_depth", type="integer")
     */
    protected $maxDepth;
    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=1000)
     */
    protected $url = '';
    /**
     * @var string
     *
     * @ORM\Column(name="filename", type="string", length=100)
     */
    protected $filename = '';

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
    protected $ready; // Review: join with $status and remove this field

    /**
     * @var bool
     *
     * @ORM\Column(name="only_domain", type="boolean")
     */
    protected $onlyDomain;
    /**
     * @var bool
     *
     * @ORM\Column(name="clear_scripts", type="boolean")
     */
    protected $clearScripts;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    protected $date;


    public function __toString() {
        return json_encode($this->jsonSerialize());
    }

    // Review: join with __toString()
    public function jsonSerialize() {
        return array("id" => $this->id, "maxDepth" => $this->maxDepth, "status" => $this->status, "onlyDomain" => $this->onlyDomain,
            "clearScripts" => $this->clearScripts, "date" => $this->date, "url" => $this->url,"filename" => $this->filename, "ready" => $this->ready);
    }

    public function __construct() {
        if (func_get_arg(0)) {
            $data = func_get_arg(0);
            $this->maxDepth = $data['maxDepth'];
            $this->url = $data['url'];
            $this->status = $data['status'];
            $this->onlyDomain = $data['onlyDomain'];
            $this->clearScripts = $data['clearScripts'];
        }
        $this->date = new \DateTime();
        $this->ready = false;
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
     * Set maxDepth
     *
     * @param integer $maxDepth
     * @return Task //REVU: TASK -> PAGE
     */
    public function setMaxDepth($maxDepth) {
        $this->maxDepth = $maxDepth;

        return $this;
    }

    /**
     * Get maxDepth
     *
     * @return integer
     */
    public function getMaxDepth() {
        return $this->maxDepth;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return Task
     */
    public function setUrl($url) {
        $this->url = $url;

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
     * @param string $filename
     */
    public function setFilename($filename) {
        $this->filename = $filename;
    }

    /**
     * @return string
     */
    public function getFilename() {
        return $this->filename;
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
     * Set onlyDomain
     *
     * @param boolean $onlyDomain
     * @return Task
     */
    public function setOnlyDomain($onlyDomain) {
        $this->onlyDomain = $onlyDomain;

        return $this;
    }

    /**
     * Get onlyDomain
     *
     * @return boolean
     */
    public function getOnlyDomain() {
        return $this->onlyDomain;
    }

    /**
     * Set clearScripts
     *
     * @param boolean $clearScripts
     * @return Task
     */
    public function setClearScripts($clearScripts) {
        $this->clearScripts = $clearScripts;

        return $this;
    }

    /**
     * Get clearScripts
     *
     * @return boolean
     */
    public function getClearScripts() {
        return $this->clearScripts;
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
}