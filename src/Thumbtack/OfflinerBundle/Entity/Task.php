<?php

namespace Thumbtack\OfflinerBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * user
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Task implements \Serializable {
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
     * @ORM\Column(name="url", type="string", length=600)
     */
    protected $url = '';
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


    public function serialize() {
        return \json_encode(array($this->id, $this->maxDepth, $this->status, $this->onlyDomain, $this->clearScripts, $this->status));
    }

    public function unserialize($serialized) {
        list($this->id, $this->maxDepth, $this->status, $this->onlyDomain, $this->clearScripts, $this->status) = \json_decode($serialized);
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set maxDepth
     *
     * @param integer $maxDepth
     * @return Task
     */
    public function setMaxDepth($maxDepth)
    {
        $this->maxDepth = $maxDepth;
    
        return $this;
    }

    /**
     * Get maxDepth
     *
     * @return integer 
     */
    public function getMaxDepth()
    {
        return $this->maxDepth;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return Task
     */
    public function setUrl($url)
    {
        $this->url = $url;
    
        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return Task
     */
    public function setStatus($status)
    {
        $this->status = $status;
    
        return $this;
    }

    /**
     * Get status
     *
     * @return string 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set ready
     *
     * @param boolean $ready
     * @return Task
     */
    public function setReady($ready)
    {
        $this->ready = $ready;
    
        return $this;
    }

    /**
     * Get ready
     *
     * @return boolean 
     */
    public function getReady()
    {
        return $this->ready;
    }

    /**
     * Set onlyDomain
     *
     * @param boolean $onlyDomain
     * @return Task
     */
    public function setOnlyDomain($onlyDomain)
    {
        $this->onlyDomain = $onlyDomain;
    
        return $this;
    }

    /**
     * Get onlyDomain
     *
     * @return boolean 
     */
    public function getOnlyDomain()
    {
        return $this->onlyDomain;
    }

    /**
     * Set clearScripts
     *
     * @param boolean $clearScripts
     * @return Task
     */
    public function setClearScripts($clearScripts)
    {
        $this->clearScripts = $clearScripts;
    
        return $this;
    }

    /**
     * Get clearScripts
     *
     * @return boolean 
     */
    public function getClearScripts()
    {
        return $this->clearScripts;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Task
     */
    public function setDate($date)
    {
        $this->date = $date;
    
        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime 
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set user
     *
     * @param \Thumbtack\OfflinerBundle\Entity\User $user
     * @return Task
     */
    public function setUser(\Thumbtack\OfflinerBundle\Entity\User $user = null)
    {
        $this->user = $user;
    
        return $this;
    }

    /**
     * Get user
     *
     * @return \Thumbtack\OfflinerBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }
}