<?php
namespace Screeper\PlayerBundle\Command;

use Screeper\ServerBundle\Services\ServerService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CheckFileCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('screeper:player:checkFile')
            ->setDescription('Fait une verification des fichiers de ScreeperPlugin')
            ->addArgument('server', InputArgument::OPTIONAL, 'Nom du serveur')
            ->addArgument('fileAdress', InputArgument::OPTIONAL, 'Adresse du fichier depuis la racine du serveur')
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
        $adress = ($input->getArgument('fileAdress')) ? $input->getArgument('fileAdress') : '';

        $container = $this->getContainer();
        $checkConnection = $container->get('screeper.json_api.services.api')->getServerStatus(str_replace("ftp_", "", $server));

        if($checkConnection)
            $container->get('screeper.player.services.player')->checkFileAction($server, $adress, $output);
        else
            $output->writeln("Probleme de connexion au serveur");
    }
}
