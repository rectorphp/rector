<?php

declare (strict_types=1);
namespace Rector\Core\Console;

use RectorPrefix202304\Symfony\Component\Console\Command\Command;
/**
 * @api
 */
final class ExitCode
{
    /**
     * @var int
     */
    public const SUCCESS = Command::SUCCESS;
    /**
     * @var int
     */
    public const FAILURE = Command::FAILURE;
    /**
     * @var int
     */
    public const CHANGED_CODE = 2;
}
