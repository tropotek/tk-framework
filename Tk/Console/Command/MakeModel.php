<?php
namespace Tk\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeModel extends MakeInterface
{

    protected function configure()
    {
        $this->setName('make-model')
            ->setAliases(['mm'])
            ->setDescription('Create a PHP Model Class from the DB schema');
        parent::configure();
    }

    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);
        $this->makeModel();
        //$this->makeMapper();

        return Command::SUCCESS;
    }

}
