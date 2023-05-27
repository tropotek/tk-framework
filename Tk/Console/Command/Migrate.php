<?php
namespace Tk\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tk\Console\Console;
use Tk\Db\Util\SqlBackup;
use Tk\Db\Util\SqlMigrate;
use Tk\Log\ConsoleOutputLogger;

class Migrate extends Console
{

    protected function configure()
    {
        $this->setName('migrate')
            ->setAliases(array('mgt'))
            ->setDescription('Migrate the DB file for this project and its dependencies');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $output->setVerbosity(OutputInterface::VERBOSITY_VERY_VERBOSE);

            $db = $this->getFactory()->getDb();

            $drop = false;
            $tables = $db->getTableList();

            if (count($tables)) {
                $drop = $this->askConfirmation('Replace the existing database. WARNING: Existing data tables will be deleted! [N]: ', false);
            }

            if ($drop) {
                $exclude = [];
                if ($this->getConfig()->isDebug()) {
                    $exclude = [$this->getConfig()->get('session.db_table')];
                }
                $db->dropAllTables(true, $exclude);
                $this->write('Mode: Install');
            } else {
                $this->write('Mode: Upgrade');
            }

            // Migrate new SQL files
            $this->write('Migration Starting.');
            $migrateList = $this->getConfig()->get('db.migrate.paths');
            $outputLogger = new ConsoleOutputLogger($output);
            $migrate = new SqlMigrate($db, $outputLogger);
            $migrate->migrateList($migrateList);

            // Execute static files
            $config = $this->getConfig();
            $dbBackup = new SqlBackup($db);
            foreach ($config->get('db.migrate.static') as $file) {
                $path = "{$config->getBasePath()}/src/config/sql/{$file}";
                if (is_file($path)) {
                    $this->writeGreen('Applying ' . $file);
                    $dbBackup->restore($path);
                }
            }

            $devFile = $this->getSystem()->makePath($config->get('debug.script'));
            if ($config->isDebug() && is_file($devFile)) {
                $this->writeBlue('  - Setup dev environment: ' . $config->get('debug.script'));
                include($devFile);
            }

            $this->write('Migration Complete.');
        } catch (\Exception $e) {
            $this->writeError($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

}
