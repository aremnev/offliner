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
class User implements UserInterface, \Serializable {
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255)
     */
    protected $email;
    /**
     * @var string
     *
     * @ORM\Column(name="photo", type="string", length=255)
     */
    protected $photo = '';

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=255)
     */
    protected $username;
    /**
     * @var string
     *
     * @ORM\Column(name="nickname", type="string", length=255)
     */
    protected $nickname = '';
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="join_date", type="datetime")
     */
    protected $joinDate;
    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Task", mappedBy="user")
     */
    protected $tasks = '';

    /**
     * Constructor
     */
    public function __construct() {
        $this->tasks = new ArrayCollection();
    }

    /**
     * Add tasks
     *
     * @param \Thumbtack\OfflinerBundle\Entity\Task $tasks
     * @return User
     */
    public function addTask(\Thumbtack\OfflinerBundle\Entity\Task $tasks) {
        $this->tasks[] = $tasks;

        return $this;
    }

    /**
     * Remove tasks
     *
     * @param \Thumbtack\OfflinerBundle\Entity\Task $tasks
     */
    public function removeTask(\Thumbtack\OfflinerBundle\Entity\Task $tasks) {
        $this->tasks->removeElement($tasks);
    }

    /**
     * Get tasks
     *
     * @return \Doctrine\Common\Collections\Collection
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

    /**
     * Set email
     *
     * @param string $email
     * @return User
     */
    public function setEmail($email) {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * Set username
     *
     * @param string $username
     * @return User
     */
    public function setUsername($username) {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername() {
        return $this->username;
    }

    /**
     * Set nickname
     *
     * @param string $nickname
     * @return User
     */
    public function setNickname($nickname) {
        $this->nickname = $nickname;

        return $this;
    }

    /**
     * Get nickname
     *
     * @return string
     */
    public function getNickname() {
        return $this->nickname;
    }

    /**
     * Set joinDate
     *
     * @param \DateTime $joinDate
     * @return User
     */
    public function setJoinDate($joinDate) {
        $this->joinDate = $joinDate;

        return $this;
    }

    /**
     * Get joinDate
     *
     * @return \DateTime
     */
    public function getJoinDate() {
        return $this->joinDate;
    }

    /**
     * Set photo
     *
     * @param string $photo
     * @return User
     */
    public function setPhoto($photo) {
        $this->photo = $photo;

        return $this;
    }

    /**
     * Get photo
     *
     * @return string
     */
    public function getPhoto() {
        return $this->photo;
    }

    //------UserInterface implement
    public function getRoles() {
        if ($this->username == 'xplk90@gmail.com') {
            return array('ROLE_ADMIN', 'UASHE_KRASAVCHIK', 'ROLE_OAUTH_USER');
        }
        return array('ROLE_USER', 'POCHTI_KRASAVCHIK', 'ROLE_OAUTH_USER');
    }

    /**
     * {@inheritDoc}
     */
    public function getPassword() {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getSalt() {
        return $this->id;
    }

    /**
     * {@inheritDoc}
     */
    public function eraseCredentials() {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function equals(UserInterface $user) {
        return $user->getEmail() === $this->email;
    }

    public function __toString() {
        return $this->nickname;
    }

    public function serialize() {
        return \json_encode(array($this->username, $this->email, $this->photo, $this->nickname, $this->joinDate, $this->id));
    }

    public function unserialize($serialized) {
        list($this->username, $this->email, $this->photo, $this->nickname, $this->joinDate, $this->id) = \json_decode($serialized);
    }


}