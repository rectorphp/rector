<?php

declare(strict_types=1);

namespace Rector\Compiler\Console;

use Rector\Compiler\Console\Command\CompileCommand;
use Symfony\Component\Console\Application;

final class RectorCompilerConsoleApplication extends Application
{
    public function __construct(CompileCommand $compileCommand)
    {
        parent::__construct('Rector Compiler', 'v1.0');

        $this->add($compileCommand);
        $commandClass = get_class($compileCommand);
        $this->setDefaultCommand($commandClass, true);
    }
}
