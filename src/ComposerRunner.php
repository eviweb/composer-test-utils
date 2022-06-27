<?php

namespace Eviweb\Composer\Testing;

use Composer\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Output\ConsoleOutput;

class ComposerRunner
{
    private string $workingdir;
    private Application $composer;
    private ?bool $success;

    public function __construct()
    {
        $this->composer = new Application();
        $this->workingdir = getcwd();
        $this->success = null;
    }

    public function setWorkingDirectory(string $workingdir): ComposerRunner
    {
        $this->workingdir = $workingdir;

        return $this;
    }

    public function run(string $command, ?string ...$options): string
    {
        return $this->configureRuntimeEnvironment()
            ->doRun($command, ...$options);
    }

    public function succeed(): ?bool
    {
        return $this->success;
    }

    public function failed(): ?bool
    {
        return is_null($this->success) ? null : !$this->success;
    }

    private function doRun(string $command, ?string ...$options): string
    {
        $input = new ArgvInput(['', '-n', '-d', $this->workingdir, $command, ...$options]);
        $output = $this->createOutput();
        $this->composer->setAutoExit(false);
        $this->success = $this->composer->run($input, $output) === 0;

        return $this->getResultFromOutput($output);
    }

    private function configureRuntimeEnvironment(): ComposerRunner
    {
        putenv('COMPOSER_HOME=' . $this->getComposerCommandPath());
        ini_set('memory_limit', '-1');

        return $this;
    }

    private function getComposerCommandPath(): string
    {
        return realpath(__DIR__ . '/../../vendor/bin/composer');
    }

    private function getResultFromOutput(ConsoleOutput $output): string
    {
        $result = $this->getOutputStreamContent($output);

        return empty($result) ? $this->getErrorFromOutput($output) : $result;
    }

    private function getErrorFromOutput(ConsoleOutput $output): string
    {
        return $this->getOutputStreamContent($output->getErrorOutput());
    }

    private function getOutputStreamContent(StreamOutput $output): string
    {
        rewind($output->getStream());
        $result = stream_get_contents($output->getStream());

        return $result ?? '';
    }

    private function createOutput(int $verbosity = ConsoleOutput::VERBOSITY_NORMAL, bool $decorated = false): ConsoleOutput
    {
        $output = new ConsoleOutput($verbosity, $decorated);

        $errorOutput = new StreamOutput(fopen('php://memory', 'w', false));
        $errorOutput->setFormatter($output->getFormatter());
        $errorOutput->setVerbosity($output->getVerbosity());
        $errorOutput->setDecorated($output->isDecorated());

        $reflectedOutput = new \ReflectionObject($output);
        $strErrProperty = $reflectedOutput->getProperty('stderr');
        $strErrProperty->setValue($output, $errorOutput);

        $reflectedParent = $reflectedOutput->getParentClass();
        $streamProperty = $reflectedParent->getProperty('stream');
        $streamProperty->setValue($output, fopen('php://memory', 'w', false));

        return $output;
    }
}
