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
     * @ORM\Column(name="url", type="string", length=600)
     */
    protected $status = '';

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
}