<?php

declare(strict_types=1);

namespace Rector\Linter;

use Rector\Exception\Linter\LintingException;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * Inspired by https://github.com/keradus/PHP-CS-Fixer/blob/a838434c7584e4744e2fca9950687d5326212560/Symfony/CS/Linter/Linter.php
 */
final class Linter
{
    /**
     * @var string
     */
    private $executable;

    public function __construct(PhpExecutableFinder $phpExecutableFinder)
    {
        $executable = $phpExecutableFinder->find();
        if ($executable === false) {
            throw new LintingException('PHP executable was not found');
        }

        $this->executable = $executable;
    }

    public function lintFile(SmartFileInfo $smartFileInfo): void
    {
        $process = new Process([$this->executable, '-l', $smartFileInfo->getRealPath()]);
        $process->run();

        if ($process->isSuccessful()) {
            return;
        }

        throw new LintingException($process->getOutput(), (int) $process->getExitCode());
    }
}
