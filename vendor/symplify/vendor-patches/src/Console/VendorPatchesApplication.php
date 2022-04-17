<?php

declare (strict_types=1);
namespace RectorPrefix20220417\Symplify\VendorPatches\Console;

use RectorPrefix20220417\Symfony\Component\Console\Application;
use RectorPrefix20220417\Symfony\Component\Console\Command\Command;
final class VendorPatchesApplication extends \RectorPrefix20220417\Symfony\Component\Console\Application
{
    /**
     * @param Command[] $commands
     */
    public function __construct(array $commands)
    {
        $this->addCommands($commands);
        parent::__construct();
    }
}
