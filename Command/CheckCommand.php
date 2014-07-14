<?php
namespace Screeper\PlayerBundle\Command;

use Screeper\ServerBundle\Services\ServerService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CheckCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('screeper:check_player')
            ->setDescription('Fait une verif des joueurs en ligne')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $checkConnection = $container->get('screeper.json_api.services.api')->getServerStatus($action->getServerName());

        if($input->getArgument('server') != null)
            $server = $input->getArgument('server');
        else
            $server = ServerService::DEFAULT_SERVER_NAME;

        if($checkConnection)
            $container->get('screeper.player.services.player')->checkOnlinePlayers($server);
    }
}