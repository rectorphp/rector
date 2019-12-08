<?php

declare(strict_types=1);

namespace Rector\Compiler\Process;

use Rector\Compiler\Contract\Process\ProcessInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

final class ProcessFactory
{
    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct()
    {
        $this->output = new NullOutput();
    }

    /**
     * @param string[] $command
     */
    public function create(array $command, string $cwd): ProcessInterface
    {
        return new SymfonyProcess($command, $cwd, $this->output);
    }

    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }
}
