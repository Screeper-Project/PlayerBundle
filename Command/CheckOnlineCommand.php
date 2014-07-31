<?php
namespace Screeper\PlayerBundle\Command;

use Screeper\ServerBundle\Services\ServerService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CheckOnlineCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('screeper:player:checkOnline')
            ->setDescription('Fait une verification de tous les joueurs en ligne')
            ->addArgument(
                'server', InputArgument::OPTIONAL, 'Nom du serveur'
            )
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $server = ($input->getArgument('server')) ? $input->getArgument('server') : ServerService::DEFAULT_SERVER_KEY;

        $container = $this->getContainer();
        $checkConnection = $container->get('screeper.json_api.services.api')->getServerStatus($server);

        if($checkConnection)
            $container->get('screeper.player.services.player')->checkOnlinePlayers($server, $output);
    }
}
