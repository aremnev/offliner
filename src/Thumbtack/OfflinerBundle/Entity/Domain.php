<?php

namespace Thumbtack\OfflinerBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

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
     * JSON string
     * @var string
     *
     * @ORM\Column(name="statistics", type="string", length=1000)
     */
    protected $statistics = '';
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    protected $date;
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="refresh_date", type="datetime")
     */
    protected $refreshDate;

    public function __toString() {
        return (string)$this->id;
    }

    // Review: where is it used? read YAGNI principle -- It is a very necessary thing for compiling json string on request
    public function jsonSerialize() {
        return array(
            "id" => $this->id,
            "status" => $this->status,
            "date" => $this->date,
            "url" => $this->url,
            "refreshDate" => $this->refreshDate
        );
    }

    public function __construct() {
        $this->date = new \DateTime();
        $this->refreshDate = new \DateTime('2000-01-01');
        // REVIEW: where it is used --Resolved
        //$this->ready = false;
        $this->pages = new ArrayCollection(); //Review: replace by native array() -- ArrayCollection is needed for lazy loading and etc Doctrine features
    }

    /**
     * Add page
     *
     * @param Page $page
     * @return Domain
     */
    public function addPage(Page $page) {
        $this->pages[] = $page;

        return $this;
    }

    /**
     * Remove page
     *
     * @param Page $page
     * @return Domain
     */
    public function removePage(Page $page) {
        $this->pages->removeElement($page);

        return $this;
    }

    /**
     * Get pages
     *
     * @return ArrayCollection
     */
    public function getPages() {
        return $this->pages;
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
     * @return Domain
     */
    public function setUrl($url) {
        $this->url = $url;
        // REVIEW: if only one url parameter is needed - use second param of parse_url(), like that - PHP_URL_HOST --Resolved
        $this->host = parse_url($url, PHP_URL_HOST);

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
     * Get url
     *
     * @return string
     */
    public function getHost() {
        return $this->host;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return Domain
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
     * @param string $statistics
     *
     * @return Domain
     */
    public function setStatistics($statistics) {
        $this->statistics = $statistics;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatistics() {
        return $this->statistics;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Domain
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
     * @param User $user
     * @return Domain
     */
    public function setUser(User $user = null) {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * Set refreshDate
     *
     * @param \DateTime $refreshDate
     * @return Domain
     */
    public function setRefreshDate($refreshDate) {
        $this->refreshDate = $refreshDate;

        return $this;
    }

    /**
     * Get refreshDate
     *
     * @return \DateTime
     */
    public function getRefreshDate() {
        return $this->refreshDate;
    }
}