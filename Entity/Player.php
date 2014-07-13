<?php

namespace Screeper\PlayerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Player
 *
 * @ORM\Table()
 * @ORM\Entity
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
     * La liste des pseudo connus pour le joueur, la dernière entrée est le dernier pseudo connu du joueur
     * @var array
     *
     * @ORM\Column(name="pseudo", type="array")
     */
    private $pseudo;

    /**
     * Pour chaque changement de pseudo, correspond à la date constaté du changement par le serveur (Chaque indice correspond a l'indice du pseudo correspondant)
     * @var array
     *
     * @ORM\Column(name="pseudo_date", type="array")
     */
    private $pseudoDate;

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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set pseudo
     *
     * @param array $pseudo
     * @return Player
     */
    public function setPseudo($pseudo)
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    /**
     * Get pseudo
     *
     * @return array 
     */
    public function getPseudo()
    {
        return $this->pseudo;
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
     * Set pseudoDate
     *
     * @param array $pseudoDate
     * @return Player
     */
    public function setPseudoDate($pseudoDate)
    {
        $this->pseudoDate = $pseudoDate;

        return $this;
    }

    /**
     * Get pseudoDate
     *
     * @return array 
     */
    public function getPseudoDate()
    {
        return $this->pseudoDate;
    }
}