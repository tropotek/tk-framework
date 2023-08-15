<?php
namespace Tk\Console;

use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tk\Traits\SystemTrait;

abstract class Console extends Command
{
    use SystemTrait;

    protected ?OutputInterface $output = null;

    protected ?InputInterface $input = null;

    protected string $cwd = '';


    /**
     * Initializes the command just after the input has been validated.
     *
     * This is mainly useful when a lot of commands extends one main command
     * where some things need to be initialized based on the input arguments and options.
     *
     * @throws \Exception
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->cwd = getcwd();
        $this->input = $input;
        $this->output = $output;
        $this->writeInfo($this->getName());

    }


    public function getOutput(): ?OutputInterface
    {
        return $this->output;
    }

    public function getInput(): ?InputInterface
    {
        return $this->input;
    }

    public function getCwd(): string
    {
        return $this->cwd;
    }

    protected function askConfirmation($msg, $default = false)
    {
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion($msg, $default);
        return $helper->ask($this->getInput(), $this->getOutput(), $question);
    }

    public function writeRed(string $str = '', int $options = OutputInterface::VERBOSITY_NORMAL)
    {
        $this->write(sprintf('<fg=red>%s</>', $str), $options);
    }

    public function writeGrey(string $str = '', int $options = OutputInterface::VERBOSITY_NORMAL)
    {
        $this->write(sprintf('<fg=white>%s</>', $str), $options);
    }

    public function writeBlue(string $str = '', int $options = OutputInterface::VERBOSITY_NORMAL)
    {
        $this->write(sprintf('<fg=blue>%s</>', $str), $options);
    }

    public function writeStrongBlue(string $str = '', int $options = OutputInterface::VERBOSITY_NORMAL)
    {
        $this->write(sprintf('<fg=blue;options=bold>%s</>', $str), $options);
    }

    public function writeGreen(string $str = '', int $options = OutputInterface::VERBOSITY_NORMAL)
    {
        $this->write(sprintf('<fg=green>%s</>', $str), $options);
    }

    public function writeGreenStrong(string $str = '', int $options = OutputInterface::VERBOSITY_NORMAL)
    {
        $this->write(sprintf('<fg=green;options=bold>%s</>', $str), $options);
    }

    public function writeStrong(string $str = '', int $options = OutputInterface::VERBOSITY_NORMAL)
    {
        $this->write(sprintf('<options=bold>%s</>', $str), $options);
    }

    public function writeInfo(string $str = '', int $options = OutputInterface::VERBOSITY_NORMAL)
    {
        $this->write(sprintf('<info>%s</info>', $str), $options);
    }

    public function writeStrongInfo(string $str = '', int $options = OutputInterface::VERBOSITY_NORMAL)
    {
        $this->write(sprintf('<fg=green;options=bold>%s</>', $str), $options);
    }

    public function writeComment(string $str = '', int $options = OutputInterface::VERBOSITY_NORMAL)
    {
        $this->write(sprintf('<comment>%s</comment>', $str), $options);
    }

    public function writeQuestion(string $str = '', int $options = OutputInterface::VERBOSITY_NORMAL)
    {
        $this->write(sprintf('<question>%s</question>', $str), $options);
    }

    public function writeError(string $str = '', int $options = OutputInterface::VERBOSITY_NORMAL)
    {
        $this->write(sprintf('<error>%s</error>', $str), $options);
    }

    public function write(string $str = '', int $options = OutputInterface::VERBOSITY_NORMAL)
    {
        if ($this->output)
            $this->output->writeln($str, $options);
    }
}
