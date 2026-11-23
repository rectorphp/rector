<?php

declare (strict_types=1);
namespace Rector\Console;

final class ExitCode
{
    /**
     * @var int
     */
    public const SUCCESS = 0;
    /**
     * @var int
     */
    public const FAILURE = 1;
    /**
     * @var int
     */
    public const CHANGED_CODE = 2;
}
