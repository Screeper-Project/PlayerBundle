<?php

namespace Screeper\PlayerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Player
 *
 * @ORM\Table(name="screeper__players", indexes={
 *     @ORM\Index(name="uuid_idx", columns={"uuid"}),
 *     @ORM\Index(name="lastusername_idx", columns={"last_username"}),
 * })
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 */
class Player
{
    const MAX_RECORDS = 5;

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
     * Pour chaque changement de pseudo, correspond à la date constaté du changement par le serveur (Chaque indice correspond a l'indice du pseudo correspondant)
     * @var array
     *
     * @ORM\Column(name="usernames_logs", type="array")
     */
    private $usernamesLogs = array();

    /**
     * Le dernier pseudo connu du joueur
     * @var string
     *
     * @ORM\Column(name="last_username", type="string", length=16)
     */
    private $lastUsername = '';

    /**
     * Le uuid du joueur, son identifiant unique
     * @var string
     *
     * @ORM\Column(name="uuid", type="string", length=255)
     */
    private $uuid;

    /**
     * Les 5 dernières connexions connu du joueur
     * REQUIRE SCREEPERPLUGIN
     *
     * @var array
     *
     * @ORM\Column(name="connections", type="array", nullable=true)
     */
    private $connections = array();

    /**
     * Les 5 dernières ips du joueur (chaque clé correspond a la clé de la connexion de "$connexions")
     * REQUIRE SCREEPERPLUGIN
     *
     * @var array
     *
     * @ORM\Column(name="ips", type="array", nullable=true)
     */
    private $ips = array();

    /**
     * La dernière connexion connu du joueur
     * REQUIRE SCREEPERPLUGIN
     *
     * @var datetime
     *
     * @ORM\Column(name="last_connection", type="datetime", nullable=true)
     */
    private $lastConnection = null;

    /**
     * La dernière ip connu du joueur
     * REQUIRE SCREEPERPLUGIN
     *
     * @var string
     *
     * @ORM\Column(name="last_ip", type="string", length=255, nullable=true)
     */
    private $lastIp = null;

    /**
     * Nombre de vérification éxécuté sur le joueur
     * @var integer
     *
     * @ORM\Column(name="nb_verification", type="integer")
     */
    private $nbVerification;

    /**
     * Dernière vérification éfféctué sur le joueur
     * @var \DateTime
     *
     * @ORM\Column(name="last_verification", type="datetime")
     */
    private $lastVerification;

    /**
     * @ORM\PreUpdate
     * @ORM\PrePersist
     * @ORM\PostLoad
     */
    public function updateLastUsername()
    {
        $usernames = $this->getUsernames();
        $new_username = end($usernames);

        if($this->getLastUsername() != $new_username)
            $this->setLastUsername($new_username);
    }

    /**
     * @ORM\PreUpdate
     * @ORM\PrePersist
     * @ORM\PostLoad
     */
    public function updateLastConnection()
    {
        if($this->getConnections() != null)
        {
            $connections = $this->getConnections();
            $new_connection = end($connections);

            if($this->getLastConnection() != $new_connection)
                $this->setLastConnection($new_connection);
        }
    }

    /**
     * @ORM\PreUpdate
     * @ORM\PrePersist
     * @ORM\PostLoad
     */
    public function updateLastIp()
    {
        if($this->getIps() != null)
        {
            $ips = $this->getIps();
            $new_ip = end($ips);

            if($this->getLastIp() != $new_ip)
                $this->setLastIp($new_ip);
        }
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
        $this->updateLastUsername();

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
     * @param \DateTime $connection
     * @return $this
     */
    public function addConnection(\DateTime $connection)
    {
        $this->connections[] = $connection;

        if(count($this->getConnections()) > self::MAX_RECORDS)
            $this->setConnections(array_shift($this->getConnections()));

        $this->updateLastConnection();

        return $this;
    }

    /**
     * @param $ip
     * @return $this
     */
    public function addIp($ip)
    {
        $this->ips[] = $ip;

        if(count($this->getIps()) > self::MAX_RECORDS)
            $this->setIps(array_shift($this->getIps()));

        $this->updateLastIp();

        return $this;
    }

    public function incrNbVerification($nb = 1)
    {
        $this->setNbVerification($this->getNbVerification() + $nb);
    }

    /* Getters et Setters */
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
     * Set lastUsername
     *
     * @param string $lastUsername
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
     * @return string
     */
    public function getLastUsername()
    {
        return $this->lastUsername;
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

    /**
     * Set connections
     *
     * @param array $connections
     * @return Player
     */
    public function setConnections($connections)
    {
        $this->connections = $connections;

        return $this;
    }

    /**
     * Get connections
     *
     * @return array
     */
    public function getConnections()
    {
        return $this->connections;
    }

    /**
     * Set ips
     *
     * @param array $ips
     * @return Player
     */
    public function setIps($ips)
    {
        $this->ips = $ips;

        return $this;
    }

    /**
     * Get ips
     *
     * @return array
     */
    public function getIps()
    {
        return $this->ips;
    }

    /**
     * Set lastConnection
     *
     * @param \DateTime $lastConnection
     * @return Player
     */
    public function setLastConnection($lastConnection)
    {
        $this->lastConnection = $lastConnection;

        return $this;
    }

    /**
     * Get lastConnection
     *
     * @return \DateTime
     */
    public function getLastConnection()
    {
        return $this->lastConnection;
    }

    /**
     * Set lastIp
     *
     * @param string $lastIp
     * @return Player
     */
    public function setLastIp($lastIp)
    {
        $this->lastIp = $lastIp;

        return $this;
    }

    /**
     * Get lastIp
     *
     * @return string
     */
    public function getLastIp()
    {
        return $this->lastIp;
    }
}
