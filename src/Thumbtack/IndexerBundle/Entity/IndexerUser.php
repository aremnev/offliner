<?php

namespace Thumbtack\IndexerBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * user
 *
 * @ORM\Table(name = "indexer_users")
 * @ORM\Entity
 */
class IndexerUser {
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
     * @ORM\OneToMany(targetEntity="Domain", mappedBy="user",cascade={"persist", "remove"})
     */
    protected $domains = null;
    /**
     * Constructor
     */
    public function __construct() {
        $this->domains = new ArrayCollection();
    }

    /**
    /**
     * Add domain
     *
     * @param Domain $domain
     * @return IndexerUser
     */
    public function addDomain(Domain $domain) {
        $this->domains[] = $domain;

        return $this;
    }

    /**
     * Remove domain
     *
     * @param Domain $domain
     * @return IndexerUser
     */
    public function removeDomain(Domain $domain) {
        $this->domains->removeElement($domain);

        return $this;
    }

    /**
     * Get domains
     *
     * @return ArrayCollection
     */
    public function getDomains() {
        return $this->domains;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    public function __toString() {
        return (string)$this->id;
    }
}