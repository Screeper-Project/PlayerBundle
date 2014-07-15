<?php

namespace Screeper\PlayerBundle\Services;

/**
 * @author Graille
 * @version 1.0.0
 * @link http://github.com/Graille
 * @package PLAYERBUNDLE
 * @since 1.0.0
 */

use Doctrine\ORM\EntityManager;
use Screeper\PlayerBundle\Entity\Player as PlayerEntity;
use Screeper\ServerBundle\Services\ServerService;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Console\Output\OutputInterface;

class PlayerService
{
    protected $container;
    protected $entityManager;

    const PLAYER_USERNAME_MAX_LENGTH = 16;
    /**
     * @param ContainerInterface $container
     * @param EntityManager $entityManager
     */
    public function __construct(ContainerInterface $container, EntityManager $entityManager)
    {
        $this->container = $container;
        $this->entityManager = $entityManager;
    }

    /**
     * Permet de savoir si un joueur est connecté en coniassant son pseudo, don uuid, ou en ayant un profil de lui
     * @param $player
     * @param string $server
     * @return bool
     * @throws \Symfony\Component\Config\Definition\Exception\InvalidTypeException
     */
    public function isConnected($player, $server_name = ServerService::DEFAULT_SERVER_NAME)
    {
        $json_api_service = $this->container->get('screeper.json_api.services.api');
        $uuid_service = $this->container->get('screeper.player.services.uuid');

        $online_players_names = $json_api_service->callResult('players.online.names', array(), $server_name);

        if($player instanceof PlayerEntity)
        {
            $last_username_of_player = strtolower($player->getLastUsername());

            foreach($online_players_names as $name)
                if($last_username_of_player == strtolower($name))
                    if($uuid_service->getUUIDFromUsername($name) == $player->getUuid())
                        return true;

            return $this->isConnected($player->getUuid()); // Si la recherche par nom simple n'a rien donnée, on recherche par uuid
        }
        elseif(is_string($player) && strlen($player) > PlayerService::PLAYER_USERNAME_MAX_LENGTH) // Si on a spécifié un uuid
        {
            foreach($online_players_names as $name)
                if($uuid_service->getUUIDFromUsername($name) == $player)
                    return true;

            return false;
        }
        elseif(is_string($player) && strlen($player) <= PlayerService::PLAYER_USERNAME_MAX_LENGTH) // Si on a spécifié un pseudo (DECONSEILLE POUR DES RAISONS DE SECURITE)
        {
            foreach($online_players_names as $name)
                if(strtolower($player) == strtolower($name))
                    return true;

            return false;
        }
        else
            throw new InvalidTypeException("Screeper - PlayerService - Vous n'avez pas spécifié ni un pseudo, ni un uuid, ni un objet de type 'Player'");
    }

    public function getPlayers(array $identifier)
    {

    }

    public function addPlayer(array $identifiers)
    {

    }

    /**
     * @param $identifier
     * @param array $options
     * @return null
     * @throws \HydrationException
     * @throws \Exception
     */
    public function getProfileInDb($identifier)
    {
        $uuid_service = $this->container->get('screeper.player.services.uuid');

        if(is_string($identifier))
            if(strlen($identifier) > 16) // Si c'est un UUID
                $result = $this->entityManager->getRepository('ScreeperPlayerBundle:Player')->findByUuid($identifier);
            else
            {
                $uuid = $uuid_service->getUUIDFromUsername($identifier);

                if($uuid == null)
                    throw new \Exception("Screeper - PlayerBundle - Erreur, l'UUID associé au pseudo spécifié est introuvable ou les serveurs de Mojang ne fonctionnent pas");

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

    public function checkOnlinePlayers($server_name, OutputInterface $output = null)
    {
        $json_api_service = $this->container->get('screeper.json_api.services.api');
        $uuid_service = $this->container->get('screeper.player.services.uuid');

        $online_players = $json_api_service->callResult('players.online.names', array(), $server_name);

        foreach($online_players as $player)
        {
            $player_uuid = $uuid_service->getUUIDFromUsername($player);

            if($player_uuid != null) // Si on a trouvé l'UUID
            {
                // On charge, s'il existe, le profile enregistré en BDD
                $profile = $this->getProfileInDb($player_uuid);

                if($output instanceof OutputInterface)
                    $output->writeln('Chargement du profil.');

                if($profile == null) // Si aucun profil n'a été trouvé, alors on en crée un.
                {
                    if($output instanceof OutputInterface)
                        $output->writeln('Aucun profil trouve pour le joueur "'.$player.'".');

                    $profile = new PlayerEntity();
                    $profile->addUsername($player)
                        ->addUsernamesLog(new \DateTime())
                        ->setUuid($player_uuid);
                }
                else {
                    $usernames = $profile->getUsernames();
                    if(end($usernames) != $player) // Si on constate un changement de pseudo, on l'enregistre
                        $profile->addUsername($player)
                            ->addUsernamesLog(new \DateTime()); }

                $profile
                    ->setNbVerification($profile->getNbVerification() + 1)
                    ->setLastVerification(new \DateTime());

                $this->entityManager->persist($profile);
                $this->entityManager->flush();

                if($output instanceof OutputInterface) {
                    $output->writeln('Le profile du joueur "'.$player.'" a ete mis a jour');
                    $output->writeln('-------------------------'); }
            }
        }
    }
}