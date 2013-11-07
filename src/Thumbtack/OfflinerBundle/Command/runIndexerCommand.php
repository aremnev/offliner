<?php
namespace Thumbtack\OfflinerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Thumbtack\OfflinerBundle\Models\OfflinerModel;
use Thumbtack\OfflinerBundle\Models\ServiceProcessor;

class runIndexerCommand extends ContainerAwareCommand{
    protected function configure()
    {
        parent::configure();
        $this->setDefinition(array())->setName('indexer:run')->setDescription('Index one page.');
        //TODO: more flexible with args and opts..
       // $this->addArgument('action', InputArgument::OPTIONAL, 'Description  of the bad work done.', 'default action'); //name, mode, description,  default
       // $this->addOption('myoption', 'shortcut-option',  InputOption::VALUE_OPTIONAL, 'Description of the option', 'default  value'); //name, shortcut, mode, description, default
    }

    protected function execute(InputInterface $input, OutputInterface $output){
        /**
         * @var ServiceProcessor $offliner
         */
        $service = $this->getContainer()->get("thumbtackServiceProcessor");
        echo $service->runIndexing();
    }
}