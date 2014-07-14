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
    public function isConnected($player, $server = ServerService::DEFAULT_SERVER_NAME)
    {
        $json_api_service = $this->container->get('screeper.json_api.services.api');
        $uuid_service = $this->container->get('screeper.player.services.uuid');
        $online_players = $json_api_service->callResult('players.online.names');

        if($player instanceof PlayerEntity)
        {
            $last_username_of_player = strtolower($player->getLastUsername());

            foreach($online_players as $name)
                if($last_username_of_player == strtolower($name))
                    if($uuid_service->getUUIDFromUsername($name) == $player->getUuid())
                        return true;

            return $this->isConnected($player->getUuid()); // Si la recherche par nom simple n'a rien donnée, on recherche par uuid
        }
        elseif(is_string($player) && strlen($player) > PlayerService::PLAYER_USERNAME_MAX_LENGTH) // Si on a spécifié un uuid
        {
            foreach($online_players as $name)
                if($uuid_service->getUUIDFromUsername($name) == $player->getUuid())
                    return true;

            return false;
        }
        elseif(is_string($player) && strlen($player) <= PlayerService::PLAYER_USERNAME_MAX_LENGTH) // Si on a spécifié un pseudo (DECONSEILLE POUR DES RAISONS DE SECURITE)
        {
            foreach($online_players as $name)
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
}