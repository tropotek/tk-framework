<?php
namespace Tk\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Tk\Console\Console;
use Tk\Db\Util\SqlBackup;
use Tk\FileUtil;

class DbBackup extends Console
{

    protected function configure()
    {
        $this->setName('dbbackup')
            ->setDescription('Call this to dump a copy of the Database sql to stdout or a file if an argument is given')
            ->addArgument('output', InputArgument::OPTIONAL, 'A file path to dump the SQL to.', null)
            ->addArgument('date_format', InputArgument::OPTIONAL, 'Auto filename generated based on date when a directory is supplied as the output. See http://php.net/manual/en/function.date.php', 'D')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $db = $this->getFactory()->getDb();
            $options = [];
            $outfile = $input->getArgument('output');
            $bak = new SqlBackup($db);

            if ($outfile) {
                if (is_dir($outfile)) {
                    $outfile = $outfile . '/' . \Tk\Date::create()->format($input->getArgument('date_format')) . '.sql';
                    $this->writeComment('  - Saving SQL to: ' . $outfile);
                } else {
                    $this->writeComment('  - Creating directory: ' . dirname($outfile));
                    FileUtil::mkdir(dirname($outfile));
                }
                $bak->save($outfile, $options);
            } else {
                $this->write($bak->dump($options));
            }
        } catch (\Exception $e) {
            $this->writeError($e->getMessage());
            return Command::FAILURE;
        }
        return Command::SUCCESS;
    }

}
