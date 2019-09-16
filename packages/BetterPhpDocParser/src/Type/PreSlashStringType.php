<?php declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Type;

use PHPStan\Type\StringType;
use PHPStan\Type\VerbosityLevel;

final class PreSlashStringType extends StringType
{
    public function describe(VerbosityLevel $verbosityLevel): string
    {
        return '\string';
    }
}
