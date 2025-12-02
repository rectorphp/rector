<?php

declare (strict_types=1);
namespace Rector\PHPUnit\Enum;

final class PHPUnitAttribute
{
    public const REQUIRES_PHP = 'PHPUnit\Framework\Attributes\RequiresPhp';
    public const REQUIRES_PHPUNIT = 'PHPUnit\Framework\Attributes\RequiresPhpunit';
    public const REQUIRES_OS = 'PHPUnit\Framework\Attributes\RequiresOperatingSystem';
    public const REQUIRES_OS_FAMILY = 'PHPUnit\Framework\Attributes\RequiresOperatingSystemFamily';
    public const REQUIRES_METHOD = 'PHPUnit\Framework\Attributes\RequiresMethod';
    public const REQUIRES_FUNCTION = 'PHPUnit\Framework\Attributes\RequiresFunction';
    public const REQUIRES_PHP_EXTENSION = 'PHPUnit\Framework\Attributes\RequiresPhpExtension';
    public const REQUIRES_SETTING = 'PHPUnit\Framework\Attributes\RequiresSetting';
    public const TEST = 'PHPUnit\Framework\Attributes\Test';
}
