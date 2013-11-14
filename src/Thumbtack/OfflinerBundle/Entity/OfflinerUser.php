<?php

namespace Thumbtack\OfflinerBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * user
 *
 * @ORM\Table(name = "offliner_users")
 * @ORM\Entity
 */
class OfflinerUser {
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
     * @ORM\OneToMany(targetEntity="Task", mappedBy="user",cascade={"persist", "remove"})
     */
    protected $tasks = null; // Review: it's should be null --Resolved
    /**
     * Constructor
     */
    public function __construct() {
        $this->tasks = new ArrayCollection();
    }
    /**
     * Add tasks
     *
     * @param Task $tasks
     * @return OfflinerUser
     */
    public function addTask(Task $tasks) {
        $this->tasks[] = $tasks;

        return $this;
    }

    /**
     * Remove tasks
     *
     * @param Task $tasks
     * @return OfflinerUser
     */
    public function removeTask(Task $tasks) {
        $this->tasks->removeElement($tasks);

        return $this;
    }

    /**
     * Get tasks
     *
     * @return ArrayCollection
     */
    public function getTasks() {
        return $this->tasks;
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