<?php
namespace Tk\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tk\Console\Console;
use Tk\Db\Util\SqlMigrate;

/**
 * @author Tropotek <http://www.tropotek.com/>
 */
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
        $this->writeComment('TODO: We need to refactor the migration system');
        return Command::SUCCESS;
        // TODO: We need to refactor the migration system

        try {
            $db = $this->getFactory()->getDb();

            $drop = false;
            $tables = $db->getTableList();

            if (count($tables)) {
                $drop = $this->askConfirmation('Replace the existing database. WARNING: Existing data tables will be deleted! [N]: ', false);
            }

//            if ($drop) {
//                $exclude = [];
//                if ($this->getConfig()->isDebug()) {
//                    $exclude = [$this->getConfig()->get('session.db_table')];
//                }
//                $db->dropAllTables(true, $exclude);
//                $this->write('Database Install...');
//            } else {
//                $this->write('Database Upgrade...');
//            }


            // Migrate new SQL files
//            $migrate = new SqlMigrate($db);
//            $migrate->setTempPath($this->getConfig()->getTempPath());
//            $migrateList = array('App Sql' => $this->getConfig()->getSrcPath() . '/config');
//            if ($this->getConfig()->get('sql.migrate.list')) {
//                $migrateList = $this->getConfig()->get('sql.migrate.list');
//            }
//
//            $mm = $this;
//            $migrate->migrateList($migrateList, function (string $str, SqlMigrate $m) use ($output, $mm) {
//                $mm->write($str);
//            });

            $this->write('Database Migration Complete.');
            $this->write('Open the site in a browser to complete the site setup: ' . \Tk\Uri::create('/')->toString());
        } catch (\Exception $e) {
            $this->writeError($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

}
