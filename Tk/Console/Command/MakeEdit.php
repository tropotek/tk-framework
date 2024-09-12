<?php
namespace Tk\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeEdit extends MakeInterface
{

    protected function configure(): void
    {
        $this->setName('make-edit')
            ->setAliases(array('me'))
            ->setDescription('Create a PHP Controller Edit Class from the DB schema');
        parent::configure();
    }

    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);
        $this->makeEdit();

        return Command::SUCCESS;
    }

}
