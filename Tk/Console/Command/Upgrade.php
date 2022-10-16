<?php
namespace Tk\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tk\Console\Console;
use Tk\Console\Exception;

/**
 * @author Tropotek <http://www.tropotek.com/>
 */
class Upgrade extends Console
{

    protected function configure()
    {
        $this->setName('upgrade')
            ->setAliases(['ug'])
            ->setDescription('Call this to upgrade the site from git and update its dependencies');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($this->getConfig()->isDebug()) {
            $this->writeError('Error: Only run this command in a live environment.');
            return Command::FAILURE;
        }
        $currentMode = $this->getRegistry()->isMaintenanceMode();

        try {
            $this->getRegistry()->setMaintenanceMode();

            // TODO: create a backup of the database before executing this.....

            $cmdList = [
                'git reset --hard',
                'git checkout master',
                'git pull',
                'git log --tags --simplify-by-decoration --pretty="format:%ci %d %h"',
                'git checkout {tag}',
                'composer update'
            ];

            if ($this->getConfig()->isDebug()) {        // For testing
                array_unshift($cmdList, 'ci');
                $cmdList[] = 'git reset --hard';
                $cmdList[] = 'git checkout master';
                $cmdList[] = 'composer update';
            }

            $tag = '';
            $output = array();
            foreach ($cmdList as $i => $cmd) {
                unset($output);
                if (preg_match('/^git log /', $cmd)) {      // find tag version
                    exec($cmd . ' 2>&1', $output, $ret);
                    foreach ($output as $line) {
                        if (preg_match('/\((tag\: )*([0-9\.]+)\)/', $line, $regs)) {
                            $tag = $regs[2];
                            break;
                        }
                    }
                    if (!$tag) {
                        throw new Exception('Error: Cannot find version tag.');
                    }
                } else {
                    if ($tag) {
                        $cmd = str_replace('{tag}', $tag, $cmd);
                    }
                    $this->writeInfo($cmd);
                    if (preg_match('/^composer /', $cmd)) {
                        system($cmd);
                    } else {
                        exec($cmd . ' 2>&1', $output, $ret);
                        if ($cmd == 'ci') {
                            continue;
                        }
                        $this->write('  ' . implode("\n  ", $output));
                    }
                }
            }

        } catch (\Exception $e) {
            $this->writeError($e->getMessage());
            return Command::FAILURE;
        } finally {
            $this->getRegistry()->setMaintenanceMode($currentMode);
        }

        return Command::SUCCESS;
    }

}
