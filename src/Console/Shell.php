<?php declare(strict_types=1);

namespace Rector\Console;

/**
 * Mimics https://github.com/cakephp/cakephp/blob/ae7ec50e12adbb30d9e7d1187b385bc32b7d31dc/src/Console/Shell.php#L39
 */
final class Shell
{
    /**
     * @var int
     */
    public const CODE_ERROR = 1;

    /**
     * @var int
     */
    public const CODE_SUCCESS = 0;
}
