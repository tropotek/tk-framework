<?php
namespace Tk\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tk\Console\Console;
use Tk\Db\Util\SqlBackup;

class Debug extends Console
{

    protected function configure()
    {
        $this->setName('debug')
            ->setDescription('(Debug) Setup the App for the development environment');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->getConfig()->isDebug()) {
            $this->writeError('Error: Only run this command in a debug environment.');
            return Command::FAILURE;
        }

        try {
            $devFile = $this->getSystem()->makePath($this->getConfig()->get('debug.script'));
            if (is_file($devFile)) {
                $this->writeComment('  - Setup dev environment: ' . $this->getConfig()->get('debug.script'));
                include($devFile);
            }
        } catch (\Exception $e) {
            $this->writeError($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

}
