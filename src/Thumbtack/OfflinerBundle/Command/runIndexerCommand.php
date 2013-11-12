<?php
namespace Thumbtack\OfflinerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Thumbtack\OfflinerBundle\Models\ServiceProcessor;

class runIndexerCommand extends ContainerAwareCommand {
    protected function configure() {
        parent::configure();
        $this->setDefinition(array())->setName('indexer:run')->setDescription('Index one page.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        /** @var ServiceProcessor $offliner */
        $service = $this->getContainer()->get("thumbtackServiceProcessor");
        echo $service->runIndexing();
    }
}