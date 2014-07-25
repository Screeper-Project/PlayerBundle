<?php

namespace Screeper\PlayerBundle\Services;

/**
 * @author Graille
 * @version 1.0.1
 * @link http://github.com/Graille
 * @package PLAYERBUNDLE
 * @since 1.0.0
 * Many sources have been inspired by https://github.com/Shadowwolf97/Minecraft-UUID-Utils/
 */


use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

class UUIDService
{
    protected $container;
    protected $entityManager;

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
     * Permet de récupérer le pseudo a partir de l'uuid, à utilisé avec précaution (erreur possible)
     * @param $identifier
     * @param int $timeout
     * @return string|null
     */
    public function getUsernameFromUUID($identifier, $timeout = 5)
    {
        if(strlen($identifier) <= 16)
            $identifier = $this->getUUIDFromUsername($identifier, $timeout)['uuid'];
        $url = "https://sessionserver.mojang.com/session/minecraft/profile/".$identifier;
        $ctx = stream_context_create(array(
                'http' => array(
                    'timeout' => $timeout
                )
            )
        );

        $ret = file_get_contents($url, 0, $ctx);

        // Verification
        if(isset($ret) && $ret != null && $ret != false)
        {
            $data = json_decode($ret, true);

            if(isset($data['name']))
                return $data['name'];
            else
                return null;
        }
        else
            return null;
    }

    /**
     * @param $username
     * @param int $timeout
     * @return string|null
     */
    public function getUUIDFromUsername($username, $timeout = 5)
    {
        if(strlen($username) > 16)
            return array("username" => "", "uuid" => "");

        $url = 'https://api.mojang.com/profiles/page/1';
        $options = array(
            'http' => array(
                'header'  => "Content-type: application/json\r\n",
                'method'  => 'POST',
                'content' => '{"name":"'.$username.'","agent":"minecraft"}',
                'timeout' => $timeout
            ),
        );

        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        // Verification
        if(isset($result) && $result != null && $result != false)
        {
            $ress = json_decode($result, true);
            $ress = $ress["profiles"][0];

            if(isset($ress['id']))
                return $ress['id'];
            else
                return null;
        }
        else
            return null;
    }

    /**
     * @param $uuid string UUID to format
     * @return string Properly formatted UUID (According to UUID v4 Standards xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx WHERE y = 8,9,A,or B and x = random digits.)
     */
    public function formatUUID($uuid)
    {
        $uid = "";
        $uid .= substr($uuid, 0, 8)."-";
        $uid .= substr($uuid, 8, 4)."-";
        $uid .= substr($uuid, 12, 4)."-";
        $uid .= substr($uuid, 16, 4)."-";
        $uid .= substr($uuid, 20);
        return $uid;
    }
}