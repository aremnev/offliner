<?php

namespace Thumbtack\AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Thumbtack\IndexerBundle\Entity\Domain;
use Thumbtack\IndexerBundle\Entity\IndexerUser;
use Thumbtack\IndexerBundle\Entity\IndexerUserInterface;
use Thumbtack\OfflinerBundle\Entity\OfflinerUser;
use Thumbtack\OfflinerBundle\Entity\OfflinerUserInterface;
use Thumbtack\OfflinerBundle\Entity\Task;

/**
 * user
 *
 * @ORM\Table(name = "users")
 * @ORM\Entity
 */
class User implements UserInterface, \Serializable,IndexerUserInterface,OfflinerUserInterface {
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
     * @ORM\Column(name="password", type="string", length=255)
     */
    protected $password;
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
     * @ORM\OneToOne(targetEntity="Thumbtack\OfflinerBundle\Entity\OfflinerUser",cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="offliner_user_id", referencedColumnName="id")
     **/
    protected $offlinerUser;
    /**
     * @ORM\OneToOne(targetEntity="Thumbtack\IndexerBundle\Entity\IndexerUser",cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="indexer_user_id", referencedColumnName="id")
     **/
    protected $indexerUser;


    /**
     * Constructor
     */
    public function __construct() {
        $this->offlinerUser = new OfflinerUser();
        $this->indexerUser = new IndexerUser();
    }
    /**
     * @return mixed
     */
    public function getIndexerUser() {
        return $this->indexerUser;
    }

    /**
     * @return mixed
     */
    public function getOfflinerUser() {
        return $this->offlinerUser;
    }
    /**
     * Add tasks
     *
     * @param Task $tasks
     * @return User
     */
    public function addTask(Task $tasks) {
        $this->offlinerUser->addTask($tasks);

        return $this;
    }

    /**
     * Remove tasks
     *
     * @param Task $tasks
     * @return User
     */
    public function removeTask(Task $tasks) {
        $this->offlinerUser->removeTask($tasks);

        return $this;
    }

    /**
     * Get tasks
     *
     * @return ArrayCollection
     */
    public function getTasks() {
        return $this->offlinerUser->getTasks();
    }

    /**
     * Add domain
     *
     * @param Domain $domain
     * @return User
     */
    public function addDomain(Domain $domain) {
        $this->indexerUser->addDomain($domain);

        return $this;
    }

    /**
     * Remove domain
     *
     * @param Domain $domain
     * @return User
     */
    public function removeDomain(Domain $domain) {
        $this->indexerUser->removeDomain($domain);

        return $this;
    }

    /**
     * Get domains
     *
     * @return ArrayCollection
     */
    public function getDomains() {
        return $this->indexerUser->getDomains();
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

    /***  UserInterface implementation ***/
    public function getRoles() {
        if ($this->username == 'xplk90@gmail.com') {
            return array('ROLE_ADMIN', 'ROLE_OAUTH_USER');
        }
        return array('ROLE_USER', 'ROLE_OAUTH_USER');
    }

    /**
     * {@inheritDoc}
     */
    public function getPassword() {
        return $this->password;
    }

    /**
     * Set password
     *
     * @param string $pass
     * @return User
     */
    public function setPassword($pass) {
        $this->password = $pass;

        return $this;
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
        return (string)$this->id;
    }

    public function serialize() {
        return \json_encode(array($this->username, $this->email, $this->password,
                                 $this->photo, $this->nickname, $this->joinDate, $this->id)
        );
    }

    public function unserialize($serialized) {
        list($this->username, $this->email, $this->password,
             $this->photo, $this->nickname, $this->joinDate, $this->id) = \json_decode($serialized);
    }
}