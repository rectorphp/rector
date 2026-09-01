<?php

declare (strict_types=1);
namespace RectorPrefix202609\Entropy\Console\Contract;

/**
 * Marks the command that runs when no command name is given on the command line.
 * Any leading non-option token is then treated as an argument of this command.
 */
interface DefaultCommandInterface extends CommandInterface
{
}
