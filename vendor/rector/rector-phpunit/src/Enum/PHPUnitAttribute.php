<?php

declare (strict_types=1);
namespace Rector\PHPUnit\Enum;

final class PHPUnitAttribute
{
    /**
     * @var string
     */
    public const REQUIRES_PHP = 'PHPUnit\Framework\Attributes\RequiresPhp';
    /**
     * @var string
     */
    public const REQUIRES_PHPUNIT = 'PHPUnit\Framework\Attributes\RequiresPhpunit';
    /**
     * @var string
     */
    public const REQUIRES_OS = 'PHPUnit\Framework\Attributes\RequiresOperatingSystem';
    /**
     * @var string
     */
    public const REQUIRES_OS_FAMILY = 'PHPUnit\Framework\Attributes\RequiresOperatingSystemFamily';
    /**
     * @var string
     */
    public const REQUIRES_METHOD = 'PHPUnit\Framework\Attributes\RequiresMethod';
    /**
     * @var string
     */
    public const REQUIRES_FUNCTION = 'PHPUnit\Framework\Attributes\RequiresFunction';
    /**
     * @var string
     */
    public const REQUIRES_PHP_EXTENSION = 'PHPUnit\Framework\Attributes\RequiresPhpExtension';
    /**
     * @var string
     */
    public const REQUIRES_SETTING = 'PHPUnit\Framework\Attributes\RequiresSetting';
    /**
     * @var string
     */
    public const TEST = 'PHPUnit\Framework\Attributes\Test';
}
