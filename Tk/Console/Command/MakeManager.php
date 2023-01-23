<?php
namespace Tk\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Tropotek <http://www.tropotek.com/>
 */
class MakeManager extends MakeInterface
{

    protected function configure()
    {
        $this->setName('make-manager')
            ->setAliases(array('mg'))
            ->setDescription('Create a PHP Controller Manager Class from the DB schema');
        parent::configure();
    }

    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);
        $this->makeManager();

        return Command::SUCCESS;
    }

}
