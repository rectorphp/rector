<?php

declare (strict_types=1);
namespace Rector\Console;

use RectorPrefix202506\Symfony\Component\Console\Command\Command;
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
