<?php

namespace Thumbtack\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * process
 *
 * @ORM\Table(name="service_info")
 * @ORM\Entity
 */
class Process {
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

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