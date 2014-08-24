<?php

namespace Screeper\PlayerBundle\Services;

/**
 * @author Graille
 * @version 1.1
 * @link http://github.com/Graille
 * @package PLAYERBUNDLE
 * @since 1.0.0
 */

use Doctrine\ORM\EntityManager;
use Screeper\PlayerBundle\Entity\Player as PlayerEntity;
use Screeper\ServerBundle\Entity\Server;
use Screeper\ServerBundle\Services\ServerService;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Exception\InvalidArgumentException;
use Symfony\Component\Console\Output\OutputInterface;

class PlayerService
{
    protected $container;
    protected $entityManager;
    protected $translator;

    const PLAYER_USERNAME_MAX_LENGTH = 16;

    /**
     * @param ContainerInterface $container
     * @param EntityManager $entityManager
     */
    public function __construct(ContainerInterface $container, EntityManager $entityManager)
    {
        $this->container = $container;
        $this->entityManager = $entityManager;
        $this->translator = $container->get('translator');
    }

    /**
     * Permet de savoir si un joueur est connecté en coniassant son pseudo, don uuid, ou en ayant un profil de lui
     * @param $identifier
     * @param string $server_name
     * @return bool
     * @throws \Symfony\Component\Config\Definition\Exception\InvalidTypeException
     */
    public function isConnected($identifier, $server_name = ServerService::DEFAULT_SERVER_KEY)
    {
        $json_api_service = $this->container->get('screeper.json_api.services.api');
        $uuid_service = $this->container->get('screeper.player.services.uuid');

        $online_players_names = $json_api_service->callResult('players.online.names', array(), $server_name);

        if($identifier instanceof PlayerEntity)
        {
            foreach($online_players_names as $name)
                if(strtolower($identifier->getLastUsername()) == strtolower($name))
                    if($uuid_service->getUUIDFromUsername($name) == $identifier->getUuid())
                        return true;

            return $this->isConnected($identifier->getUuid()); // Si la recherche par nom simple n'a rien donnée, on recherche par uuid
        }
        elseif(is_string($identifier) && strlen($identifier) > self::PLAYER_USERNAME_MAX_LENGTH) // Si on a spécifié un uuid
        {
            foreach($online_players_names as $name)
                if($uuid_service->getUUIDFromUsername($name) == $identifier)
                    return true;

            return false;
        }
        elseif(is_string($identifier) && strlen($identifier) <= self::PLAYER_USERNAME_MAX_LENGTH) // Si on a spécifié un pseudo (DECONSEILLE POUR DES RAISONS DE SECURITE)
        {
            foreach($online_players_names as $name)
                if(strtolower($identifier) == strtolower($name))
                    return true;

            return false;
        }
        else
            throw new InvalidTypeException("Screeper - PlayerService - ".$this->translator->trans('service.player.missing.informations'));
    }

    /**
     * @param $player_uuid
     * @param $player_username
     * @return PlayerEntity
     */
    public function addPlayer($player_uuid, $player_username)
    {
        $player = $this->createPlayer($player_uuid, $player_username);
        $this->entityManager->persist($player);
        $this->entityManager->flush();

        return $player;
    }

    /**
     * @param $player_uuid
     * @param $player_username
     * @param null $date
     * @return PlayerEntity
     */
    public function createPlayer($player_uuid, $player_username, $date = null)
    {
        if($date == null) $date = new \DateTime();

        $player = new PlayerEntity();
        $player->addUsername($player_username)
            ->addUsernamesLog($date)
            ->setUuid($player_uuid);

        return $player;
    }

    /**
     * @param $identifier
     * @return null
     * @throws \Symfony\Component\Config\Definition\Exception\InvalidTypeException
     * @throws \Exception
     */
    public function getProfileInDb($identifier)
    {
        $uuid_service = $this->container->get('screeper.player.services.uuid');

        if(is_string($identifier))
            if(strlen($identifier) > self::PLAYER_USERNAME_MAX_LENGTH) // Si c'est un UUID
                $result = $this->entityManager->getRepository('ScreeperPlayerBundle:Player')->findByUuid($identifier);
            else
            {
                $uuid = $uuid_service->getUUIDFromUsername($identifier);

                if(!$uuid) return null;

                return $this->getProfileInDb($uuid);
            }
        else
            throw new InvalidTypeException("Screeper - PlayerBundle - L'argument passer pour récupéré un profil doit etre un pseudo ou un uuid");

        $nb_result = count($result);

        if($nb_result == 0) // Si on a aucun résultat, on retourne null, le joueur n'a jamais été enregistré en bdd
            return null;
        if($nb_result == 1) // Si on a un seul résultat, on le retourne
            return $result[0];
        if($nb_result >= 1) // Si on a plusieurs résultats
            throw new \Exception("Screeper - PlayerBundle - Il semblerait que le serveur possède deux joueurs ayant le même UUID, veuillez contacter un administrateur");
    }

    /**
     * @param string $server_key
     * @param OutputInterface $output
     */
    public function checkOnlinePlayers($server_key = ServerService::DEFAULT_SERVER_KEY, OutputInterface $output = null)
    {
        $json_api_service = $this->container->get('screeper.json_api.services.api');
        $uuid_service = $this->container->get('screeper.player.services.uuid');

        $online_players = $json_api_service->callResult('players.online.names', array(), $server_key);
        $date = new \DateTime();

        foreach($online_players as $player_username)
        {
            $player_uuid = $uuid_service->getUUIDFromUsername($player_username);
            if(!empty($player_uuid)) $this->checkPlayer($player_uuid, $player_username, $date, array('flush' => false), $output);
        }

        $this->entityManager->flush(); // On flush tout ce qui a été persist
    }

    /**
     * @param null $player_uuid
     * @param null $player_username
     * @param null $date
     * @param array $options
     * @param OutputInterface $output
     * @return null|PlayerEntity
     * @throws \Symfony\Component\Form\Exception\InvalidArgumentException
     */
    public function checkPlayer($player_uuid = null, $player_username = null, $date = null, $options = array(), OutputInterface $output = null)
    {
        if($player_uuid != null && $player_username != null) // Si on a trouvé l'UUID
        {
            if($date == null) $date = new \DateTime();

            // On charge, s'il existe, le profile enregistré en BDD
            $profile = $this->getProfileInDb($player_uuid);

            if($output instanceof OutputInterface)
                $output->writeln('Chargement du profil.');

            if(!$profile) // Si aucun profil n'a été trouvé, alors on en crée un.
            {
                if($output instanceof OutputInterface) $output->writeln('Aucun profil trouve pour le joueur "'.$player_username.'", celui-ci va etre cree.');
                $profile = $this->createPlayer($player_uuid, $player_username, $date);
            }
            else
                if($profile->getLastUsername() != $player_username) // Si on constate un changement de pseudo, on l'enregistre
                    $profile->addUsername($player_username)
                        ->addUsernamesLog($date);

            $profile->incrNbVerification()
                ->setLastVerification(new \DateTime());

            if($output instanceof OutputInterface) {
                $output->writeln('Le profil du joueur "'.$player_username.'" a ete mis a jour');
                $output->writeln('-------------------------'); }

            // Gestion des options
            if(!isset($options['persist']) || $options['persist']) $this->entityManager->persist($profile); // default: true
            if(!isset($options['flush']) || $options['flush']) $this->entityManager->flush(); // default: true
            if(isset($options['getProfile']) && $options['getProfile']) return $profile; // default: false
            if(isset($options['getBoolean']) && $options['getBoolean']) return ($profile) ? true : false; // default: false
        }
        else
            throw new InvalidArgumentException("Screeper - PlayerBundle - checkPlayer() : Vous n'avez pas spécifié l'UUID ou le pseudo du joueur");
    }

    /* SCREEPERPLUGIN PLAYER EXTENSION */

    /**
     * @param $server
     * @param $file_adress
     * @param OutputInterface $output
     * @return bool
     */
    public function checkFileAction($server, $file_adress, OutputInterface $output = null)
    {
        if(!($server instanceof Server))
            $srv = $this->get('screeper.server.services.server')->getServer($server);

        $adress = 'ftp://' . $srv->getLogin() . ':' . $srv->getPwd() . '@' . $srv->getIp() . '/' . $file_adress;

        // Chargement du fichier
        $file = fopen($adress, 'r+');
        if(!$file) return false;

        // Traitement du fichier
        while(true)
        {
            $line = fgets($file);

            if(!$line) break;

            $infos = explode(':', $line);
            $date = new \DateTime();
            $date->setTimestamp($infos[0]);

            $profile = $this->checkPlayer($infos[2], $infos[1], $date, array(
                'getProfile' => true,
                'persist' => false,
                'flush' => false
            ), $output);

            // ScreeperPlugin
            $profile->addConnection($date)
                ->addIp($infos[3]);

            $this->entityManager->persist($profile);
        }
        $this->entityManager->flush();

        fclose($file); // Fermeture du fichier

        // Clear file
        $file = fopen($adress ,"w");
        ftruncate($file,0);
        fclose($file);

        return true;
    }
}