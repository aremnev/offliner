<?php
namespace Thumbtack\OfflinerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Thumbtack\OfflinerBundle\Models\OfflinerModel;
use Thumbtack\OfflinerBundle\Models\ServiceProcessor;

class runOfflinerTaskCommand extends ContainerAwareCommand {
    protected function configure() {
        parent::configure();
        $this->setDefinition(array())->setName('offliner:run')->setDescription('Run one task.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        /** @var ServiceProcessor $service */
        $service = $this->getContainer()->get("thumbtackOfflinerProcessor");
        echo $service->runQueueTask();
    }
}