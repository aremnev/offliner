<?php
namespace Thumbtack\IndexerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Thumbtack\IndexerBundle\Models\IndexerProcessor;

class runIndexerCommand extends ContainerAwareCommand {
    protected function configure() {
        parent::configure();
        $this->setDefinition(array())->setName('indexer:run')->setDescription('Index one page.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        /** @var IndexerProcessor $service */
        $service = $this->getContainer()->get("thumbtackIndexerProcessor");
        echo $service->runIndexing();
    }
}