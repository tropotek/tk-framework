<?php
namespace Tk\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeAll extends MakeInterface
{

    protected function configure()
    {
        $this->setName('make-all')
            ->setAliases(array('ma'))
            ->setDescription('Create all DB objects table, form and Controllers.');
        parent::configure();
    }

    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);
        $this->makeAll();

        return Command::SUCCESS;
    }

}
