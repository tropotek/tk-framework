<?php
namespace Tk\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Tk\Console\Console;
use Tk\Db\Util\ModelGenerator;
use Tk\System;

class MakeInterface extends Console
{

    protected ModelGenerator|null $gen = null;

    protected string $basePath = '';


    protected function configure(): void
    {
        $this->addArgument('table', InputArgument::REQUIRED, 'The name of the table to generate the class file from.')
            ->addOption('overwrite', 'o', InputOption::VALUE_NONE, 'Overwrite existing class files.')
            ->addOption('modelForm', 'm', InputOption::VALUE_NONE, 'Generate a ModelForm object instead')       // This object is deprecated
            ->addOption('namespace', 'N', InputOption::VALUE_OPTIONAL, 'A custom namespace (Default: App)', '')
            ->addOption('classname', 'C', InputOption::VALUE_OPTIONAL, 'A custom Classname (Default: `TableName`)', '')
            ->addOption('basepath', 'B', InputOption::VALUE_OPTIONAL, 'A base src path to save the file (Default: {sitePath}/src)', '');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->getConfig()->isDev()) {
            throw new \Exception('Error: Only run this command in a debug environment.');
        }

        $this->basePath = $input->getOption('basepath');
        if (!$this->getBasePath())
            $this->basePath = System::makePath($this->getConfig()->get('path.src'));

        $this->gen = ModelGenerator::create(
            $input->getArgument('table'),
            $input->getOption('namespace'),
            $input->getOption('classname')
        );

        return Command::SUCCESS;
    }

    protected function makeAll(): void
    {
        $this->makeModel();
        $this->makeMapper();
        $this->makeTable();
        $this->makeForm();
        $this->makeManager();
        $this->makeEdit();
    }

    protected function makeModel(): void
    {
        $file = $this->getBasePath() . '/' . str_replace('\\', '/', $this->getGen()->getDbNamespace()) . '/' . $this->getGen()->getClassName() . '.php';
        $code = $this->getGen()->makeModel($this->getInput()->getOptions());
        $file = $this->writeFile($file, $code);
        $this->writeComment('Writing Model: ' . $file);
    }

    protected function makeMapper(): void
    {
        $file = $this->getBasePath() . '/' . str_replace('\\', '/', $this->getGen()->getDbNamespace()) . '/' . $this->getGen()->getClassName() . 'Map.php';
        $code = $this->getGen()->makeMapper($this->getInput()->getOptions());
        $file = $this->writeFile($file, $code);
        $this->writeComment('Writing Mapper: ' . $file);
    }

    protected function makeForm(): void
    {
        $file = $this->getBasePath() . '/' . str_replace('\\', '/', $this->getGen()->getFormNamespace()) . '/' . $this->getGen()->getClassName() . '.php';
        $code = $this->getGen()->makeForm($this->getInput()->getOptions());
        $file = $this->writeFile($file, $code);
        $this->writeComment('Writing Form: ' . $file);
    }

    protected function makeEdit(): void
    {
        $file = $this->getBasePath() . '/' . str_replace('\\', '/', $this->getGen()->getControllerNamespace()) . '/' . $this->getGen()->getClassName() . '/Edit.php';
        $code = $this->getGen()->makeEdit($this->getInput()->getOptions());
        $file = $this->writeFile($file, $code);
        $this->writeComment('Writing Edit Form: ' . $file);
    }

    protected function makeTable(): void
    {
        $file = $this->getBasePath() . '/' . str_replace('\\', '/', $this->getGen()->getTableNamespace()) . '/' . $this->getGen()->getClassName() . '.php';
        $code = $this->getGen()->makeTable($this->getInput()->getOptions());
        $file = $this->writeFile($file, $code);
        $this->writeComment('Writing Table: ' . $file);
    }

    protected function makeManager(): void
    {
        $file = $this->getBasePath() . '/' . str_replace('\\', '/', $this->getGen()->getControllerNamespace()) . '/' . $this->getGen()->getClassName() . '/Manager.php';
        $code = $this->getGen()->makeManager($this->getInput()->getOptions());
        $file = $this->writeFile($file, $code);
        $this->writeComment('Writing Manager Form: ' . $file);
    }

    protected function writeFile(string $file, string $code): string
    {
        if (!$this->getInput()->getOption('overwrite'))
            $file = $this->makeUniquePhpFilename($file);
        if (!is_dir(dirname($file))) {
            $this->writeComment('Creating Path: ' . dirname($file));
            mkdir(dirname($file), 0777, true);
        }
        file_put_contents($file, $code);
        return $file;
    }

    public function getGen(): ModelGenerator
    {
        return $this->gen;
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }

    public function makeUniquePhpFilename(string $path): string
    {
        $i = 1;
        while (is_file($path)) {
            $path = preg_replace('/((\.[0-9]+)?\.php)$/', '.'.$i++.'.php', $path);
        };
        return $path;
    }
}
