<?php
namespace Tk\Console\Command;

use FilesystemIterator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tk\Console\Console;

class CleanData extends Console
{

    protected function configure()
    {
        $this->setName('clean-data')
            ->setAliases(['cd'])
            ->setDescription('Clean the data folder of empty folders');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->deleteEmptyFolders($this->getConfig()->getDataPath());
            $this->deleteOldFiles($this->getConfig()->getTempPath(), \Tk\Date::create()->sub(new \DateInterval('P7D')));
            if (!$this->getConfig()->isDebug())
                $this->deleteOldSessions();
        } catch (\Exception $e) {
            $this->writeError($e->getMessage());
            return Command::FAILURE;
        }
        return Command::SUCCESS;
    }

    /**
     * @throws \Exception
     */
    protected function deleteEmptyFolders(string $path)
    {
        // Recursively Remove any empty folders older than 1 hour
        if (is_dir($path)) {
            $path = rtrim($path, '/');
            $this->write('   - Removing Empty Folders: ' . $path);
            \Tk\FileUtil::removeEmptyFolders($path, function ($pth) {
                $date = \Tk\Date::create(filemtime($pth));
                $now = \Tk\Date::create()->sub(new \DateInterval('PT1H'));
                if ($date < $now) {
                    $this->write('     Deleting: [' . $date->format(\Tk\Date::FORMAT_ISO_DATETIME) . '] - ' . $pth);
                    return true;
                }
                return false;
            });
        }
    }

    protected function deleteOldFiles(string $path, \DateTime $validDate)
    {
        // clean up temp files older than $validDate
        if (is_dir($path)) {
            $dir  = new \RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS);
            $files = new \RecursiveIteratorIterator($dir, \RecursiveIteratorIterator::SELF_FIRST);
            if ($files->callHasChildren())
                $this->write('   - Removing Temp Files: ' . $path);

            /** @var \SplFileInfo $file */
            foreach ($files as $file) {
                if ($file->isDir()) continue;
                $date = \Tk\Date::create(filemtime($file));
                if ($date < $validDate) {
                    $this->write('     Deleting: [' . $date->format(\Tk\Date::FORMAT_ISO_DATETIME) . '] ' . $file->__toString());
                    unlink($file);
                }
            }
        }
    }

    /**
     * Clear any sessions in the DB that are outdated
     * This is just to ensure we do not waste space on defunct session data
     * @throws \Tk\Db\Exception
     */
    protected function deleteOldSessions()
    {
        $this->write('   - Cleaning obsolete sessions.');
        if ($this->getConfig()->get('session.db_enable', false)) {
            $db = $this->getFactory()->getDb()->getPdo();
            $expire = session_cache_expire() * 4;
            $tbl = $this->getConfig()->get('session.db_table');
            $i = $db->exec("DELETE FROM {$tbl} WHERE modified < DATE_SUB(NOW(), INTERVAL {$expire} MINUTE)");
            if ($i) {
                $this->write('     Deleted: ' . $i);
            }
        }
    }

}
