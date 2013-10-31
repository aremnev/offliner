<?php

namespace Thumbtack\OfflinerBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * task
 *
 * @ORM\Table(name="indexer_domains")
 * @ORM\Entity
 */
class Domain implements \JsonSerializable {
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Page", mappedBy="domain",cascade={"persist", "remove"})
     */
    protected $pages;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="domains")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;
    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=1000)
     */
    protected $url = '';
    /**
     * @var string
     *
     * @ORM\Column(name="host", type="string", length=1000)
     */
    protected $host = '';
    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=20)
     */
    protected $status = '';
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    protected $date;


    public function __toString() {
        return (string)$this->id;
    }
    public function jsonSerialize() {
        return array("id"=>$this->id,"status"=>$this->status,"date"=>$this->date,"url"=>$this->url);
    }
    public function __construct(){
        if(func_get_arg(0)){
            $data = func_get_arg(0);
            $this->setUrl($data['url']);
            $this->status = $data['status'];
        }
        $this->date = new \DateTime();
        $this->ready = false;
        $this->pages = new ArrayCollection();
    }
    /**
     * Add page
     *
     * @param \Thumbtack\OfflinerBundle\Entity\Page $page
     * @return User
     */
    public function addPage(\Thumbtack\OfflinerBundle\Entity\Page $page) {
        $this->pages[] = $page;

        return $this;
    }

    /**
     * Remove page
     *
     * @param \Thumbtack\OfflinerBundle\Entity\Page $page
     */
    public function removePage(\Thumbtack\OfflinerBundle\Entity\Page $page) {
        $this->tasks->removeElement($page);
    }

    /**
     * Get tasks
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPages() {
        return $this->pages;
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
        $parsed = parse_url($url);
        $this->host = $parsed['host'];
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
     * Get url
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
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