<?php

namespace Screeper\PlayerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Player
 *
 * @ORM\Table()
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 */
class Player
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * La liste des pseudos connus pour le joueur, la dernière entrée est le dernier pseudo connu du joueur
     * @var array
     *
     * @ORM\Column(name="usernames", type="array")
     */
    private $usernames = array();

    /**
     * Le dernier pseudo connu du joueur
     * @var array
     *
     * @ORM\Column(name="last_username", type="array")
     *
     */
    private $lastUsername = array();

    /**
     * Pour chaque changement de pseudo, correspond à la date constaté du changement par le serveur (Chaque indice correspond a l'indice du pseudo correspondant)
     * @var array
     *
     * @ORM\Column(name="usernames_logs", type="array")
     */
    private $usernamesLogs = array();

    /**
     * Le uuid du joueur, son identifiant unique
     * @var string
     *
     * @ORM\Column(name="uuid", type="text")
     */
    private $uuid;

    /**
     * Dernière vérification éfféctué sur le joueur
     * @var \DateTime
     *
     * @ORM\Column(name="last_verification", type="datetime")
     */
    private $lastVerification;

    /**
     * Nombre de vérification éxécuté sur le joueur
     * @var integer
     *
     * @ORM\Column(name="nb_verification", type="integer")
     */
    private $nbVerification;

    /**
     * @ORM\PreUpdate
     * @ORM\PrePersist
     */
    public function updateLastUsername()
    {
        $new_username = end($this->getUsernames());

        if($this->getLastUsername() != $new_username)
            $this->setLastUsername($new_username);
    }

    /**
     * Add username
     *
     * @param array $username
     * @return Player
     */
    public function addUsername($username)
    {
        $this->usernames[] = $username;

        return $this;
    }

    /**
     * Add usernamesLog
     *
     * @param array $usernamesLog
     * @return Player
     */
    public function addUsernamesLog($usernamesLog)
    {
        $this->usernamesLogs[] = $usernamesLog;

        return $this;
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
     * Set usernames
     *
     * @param array $usernames
     * @return Player
     */
    public function setUsernames($usernames)
    {
        $this->usernames = $usernames;

        return $this;
    }

    /**
     * Get usernames
     *
     * @return array 
     */
    public function getUsernames()
    {
        return $this->usernames;
    }

    /**
     * Set lastUsername
     *
     * @param array $lastUsername
     * @return Player
     */
    public function setLastUsername($lastUsername)
    {
        $this->lastUsername = $lastUsername;

        return $this;
    }

    /**
     * Get lastUsername
     *
     * @return array 
     */
    public function getLastUsername()
    {
        return $this->lastUsername;
    }

    /**
     * Set usernamesLogs
     *
     * @param array $usernamesLogs
     * @return Player
     */
    public function setUsernamesLogs($usernamesLogs)
    {
        $this->usernamesLogs = $usernamesLogs;

        return $this;
    }

    /**
     * Get usernamesLogs
     *
     * @return array 
     */
    public function getUsernamesLogs()
    {
        return $this->usernamesLogs;
    }

    /**
     * Set uuid
     *
     * @param string $uuid
     * @return Player
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * Get uuid
     *
     * @return string 
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * Set lastVerification
     *
     * @param \DateTime $lastVerification
     * @return Player
     */
    public function setLastVerification($lastVerification)
    {
        $this->lastVerification = $lastVerification;

        return $this;
    }

    /**
     * Get lastVerification
     *
     * @return \DateTime 
     */
    public function getLastVerification()
    {
        return $this->lastVerification;
    }

    /**
     * Set nbVerification
     *
     * @param integer $nbVerification
     * @return Player
     */
    public function setNbVerification($nbVerification)
    {
        $this->nbVerification = $nbVerification;

        return $this;
    }

    /**
     * Get nbVerification
     *
     * @return integer 
     */
    public function getNbVerification()
    {
        return $this->nbVerification;
    }
}
