<?php
namespace Tk\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Tk\Console\Console;
use Tk\Db\Util\SqlBackup;
use Tk\Uri;
use Tk\Db;

class Mirror extends Console
{

    protected function configure(): void
    {
        $this->setName('mirror')
            ->setAliases(array('mi'))
            ->addOption('no-cache', 'C', InputOption::VALUE_NONE, 'Force downloading of the live DB. (Cached for the day)')
            ->addOption('no-sql', 'S', InputOption::VALUE_NONE, 'Do not execute the sql component of the mirror')
            ->addOption('no-dev', 'f', InputOption::VALUE_NONE, 'Do not execute the dev sql file')
            ->addOption('copy-data', 'd', InputOption::VALUE_NONE, 'Copy the \'/data\' files from the live site.')
            ->setDescription('Mirror the data and files from the Live site');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $config = $this->getConfig();
            if (!$config->isDev()) {
                $this->writeError('Only run this command in a dev environment.');
                return Command::FAILURE;
            }
            if (!$this->getConfig()->get('db.mirror.secret', false)) {
                $this->writeError('Secret key not valid: ' . $this->getConfig()->get('db.mirror.secret'));
                return Command::FAILURE;
            }
            if (!$config->get('db.mirror.url', false)) {
                $this->writeError('Invalid source mirror URL: ' . $config->get('db.mirror.url'));
                return Command::FAILURE;
            }

            $backupSqlFile = $config->getTempPath() . '/tmpt.sql';
            $mirrorSqlFile = $config->getTempPath() . '/' . \Tk\Date::create()->format(\Tk\Date::FORMAT_ISO_DATE) . '-tmpl.sql.gz';

            // Delete live cached files
            $list = glob($config->getTempPath() . '/*-tmpl.sql.gz');
            foreach ($list as $file) {
                if ($input->getOption('no-cache') || $file != $mirrorSqlFile) {
                    if (is_file($file)) unlink($file);
                }
            }

            $dbBackup = new SqlBackup(Db::getPdo());
            $exclude = [$config->get('session.db_table')];

            if (!$input->getOption('no-sql')) {
                if (!is_file($mirrorSqlFile) || $input->getOption('no-cache')) {
                    $this->writeComment('Download fresh mirror file: ' . $mirrorSqlFile);
                    if (is_file($mirrorSqlFile)) unlink($mirrorSqlFile);

                    // get a copy of the remote DB to be mirrored
                    $url = Uri::create($config->get('db.mirror.url'))->set('action', 'db');
                    $this->postRequest($url, $mirrorSqlFile);

                } else {
                    $this->writeComment('Using existing mirror file: ' . $mirrorSqlFile);
                }

                // Prevent accidental writing to live DB
                $this->writeComment('Backup this DB to file: ' . $backupSqlFile);
                $dbBackup->save($backupSqlFile, ['exclude' => $exclude]);

                $this->write('Drop this DB tables');
                Db::dropAllTables(true, $exclude);

                $this->write('Import mirror file to this DB');
                $dbBackup->restore($mirrorSqlFile);

                // Execute static files
                //SqlMigrate::migrateStatic([$this, 'writeGreen']);

                // setup dev environment if site in dev mode
                //SqlMigrate::migrateDev([$this, 'writeBlue']);

                //unlink($backupSqlFile);
            }

            // if with Data, copy the data folder and its files
            if ($input->getOption('copy-data')) {

                $dataPath = $config->getDataPath();
                $dataBakPath = $dataPath . '_bak';
                $tempDataFile = $config->getBasePath() . '/dest-' . \Tk\Date::create()->format(\Tk\Date::FORMAT_ISO_DATE) . '-data.tgz';

                $this->write('Downloading live data files...[Please wait]');
                if (is_file($tempDataFile)) unlink($tempDataFile);

                $url = Uri::create($config->get('db.mirror.url'))->set('action', 'file');
                $this->postRequest($url, $tempDataFile);
                $this->write('Download Complete!');

                if (is_dir($dataBakPath)) { // Remove any old bak data folder
                    $this->write('Deleting existing data backup: ' . $dataBakPath);
                    $cmd = sprintf('rm -rf %s ', escapeshellarg($dataBakPath));
                    system($cmd);
                }
                if (is_dir($dataPath)) {    // Move existing data to data_bak
                    $this->write('Move current data files to backup location: ' . $dataBakPath);
                    $cmd = sprintf('mv %s %s ', escapeshellarg($dataPath), escapeshellarg($dataBakPath));
                    system($cmd);
                }

                if (is_dir($dataPath)) {    // Move temp data to data
                    $this->write('Extract downloaded data files to: ' . $dataPath);
                    $cmd = sprintf('cd %s && tar zxf %s ', escapeshellarg($config->getBasePath()), escapeshellarg(basename($tempDataFile)));
                    system($cmd);
                }
            }
        } catch(\Exception $e) {
            $this->writeError($e->getMessage());
            return Command::FAILURE;
        }


        $this->write('Complete!!!');
        return  Command::SUCCESS;
    }

    protected function postRequest(Uri|string $srcUrl, string $destPath)
    {
        $srcUrl = Uri::create($srcUrl)->setScheme(Uri::SCHEME_HTTP_SSL);
        $srcUrl->set('secret', $this->getConfig()->get('db.mirror.secret'));
        $query = $srcUrl->getQuery();

        $fp = fopen($destPath, 'w');
        $curl = curl_init($srcUrl->reset()->toString());
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $query);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_FILE, $fp);

        curl_exec($curl);
        if(curl_error($curl)) {
            fwrite($fp, curl_error($curl));
        }
        curl_close($curl);
        fclose($fp);
    }

}
