<?php

declare (strict_types=1);
namespace RectorPrefix202608\Entropy\Console\Contract;

/**
 * Marks a command that should not be listed in the help output, e.g. internal commands.
 */
interface HiddenCommandInterface extends CommandInterface
{
}
