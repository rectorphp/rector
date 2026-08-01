<?php

declare (strict_types=1);
namespace RectorPrefix202608\Entropy\Console\Enum;

final class ExitCode
{
    /**
     * @var int
     */
    public const SUCCESS = 0;
    /**
     * @var int
     */
    public const ERROR = 1;
    /**
     * @var int
     */
    public const INVALID_COMMAND = 2;
}
