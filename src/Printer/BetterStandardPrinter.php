<?php declare(strict_types=1);

namespace Rector\Printer;

use PhpParser\PrettyPrinter\Standard;

final class BetterStandardPrinter extends Standard
{
    /**
     * Do not preslash slashes.
     * Was causing `Vendor\Class` => `Vendor\\Class`.
     */
    protected function pSingleQuotedString(string $string): string
    {
        return '\'' . addcslashes($string, '\'') . '\'';
    }
}
