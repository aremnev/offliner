<?php
namespace Thumbtack\OfflinerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Thumbtack\OfflinerBundle\Models\OfflinerModel;
use Thumbtack\OfflinerBundle\Models\OfflinerProcessor;

class runOfflinerTaskCommand extends ContainerAwareCommand{
    protected function configure()
    {
        parent::configure();
        $this->setDefinition(array())->setName('offliner:run')->setDescription('Run one task.');
       // $this->addArgument('action', InputArgument::OPTIONAL, 'Description  of the bad work done.', 'default action'); //name, mode, description,  default
       // $this->addOption('myoption', 'shortcut-option',  InputOption::VALUE_OPTIONAL, 'Description of the option', 'default  value'); //name, shortcut, mode, description, default
    }

    protected function execute(InputInterface $input, OutputInterface $output){
        /**
         * @var OfflinerProcessor $offliner
         */
        $offliner = $this->getContainer()->get("thumbtackOfflinerProcessor");
        echo $offliner->runQueueTask();
    }
}