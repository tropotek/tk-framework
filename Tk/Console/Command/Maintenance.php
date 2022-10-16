<?php
namespace Tk\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tk\Console\Console;

/**
 * @author Tropotek <http://www.tropotek.com/>
 */
class Maintenance extends Console
{

    protected function configure()
    {
        $enabled = $this->getRegistry()->isMaintenanceMode();

        $this->setName('maintenance')
            ->setAliases(['maint'])
            ->setDescription('Enable/Disable the sites maintenance mode. Current: ' . ($enabled ? 'Enabled' : 'Disabled'));
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $mode = $this->askConfirmation('Do you wish to enable Maintenance Mode [y/n]?', false);

        if ($mode) {
            $this->writeInfo('Maintenance mode enabled.');
        } else {
            $this->writeInfo('Maintenance mode disabled.');
        }
        $this->getRegistry()->setMaintenanceMode($mode);

        return Command::SUCCESS;
    }
}
