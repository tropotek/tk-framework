<?php
namespace Tk\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Tropotek <http://www.tropotek.com/>
 */
class MakeForm extends MakeInterface
{

    protected function configure()
    {
        $this->setName('make-form')
            ->setAliases(array('mf'))
            ->setDescription('Create a PHP Form Edit Class from the DB schema');
        parent::configure();
    }

    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);
        $this->makeForm();

        return Command::SUCCESS;
    }
}
