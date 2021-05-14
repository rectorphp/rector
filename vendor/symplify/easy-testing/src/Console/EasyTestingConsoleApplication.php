<?php

declare (strict_types=1);
namespace RectorPrefix20210514\Symplify\EasyTesting\Console;

use RectorPrefix20210514\Symfony\Component\Console\Application;
use RectorPrefix20210514\Symfony\Component\Console\Command\Command;
use RectorPrefix20210514\Symplify\PackageBuilder\Console\Command\CommandNaming;
final class EasyTestingConsoleApplication extends \RectorPrefix20210514\Symfony\Component\Console\Application
{
    /**
     * @param Command[] $commands
     */
    public function __construct(\RectorPrefix20210514\Symplify\PackageBuilder\Console\Command\CommandNaming $commandNaming, array $commands)
    {
        foreach ($commands as $command) {
            $commandName = $commandNaming->resolveFromCommand($command);
            $command->setName($commandName);
            $this->add($command);
        }
        parent::__construct('Easy Testing');
    }
}
