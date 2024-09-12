<?php
namespace Tk\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeTable extends MakeInterface
{

    protected function configure(): void
    {
        $this->setName('make-table')
            ->setAliases(array('mt'))
            ->setDescription('Create a PHP Manager Table Class from the DB schema');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);
        $this->makeTable();

        return Command::SUCCESS;
    }
}
