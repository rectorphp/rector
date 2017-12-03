<?php declare(strict_types=1);

namespace Rector\Printer;

use PhpParser\PrettyPrinter\Standard;

final class BetterStandardPrinter extends Standard
{
    /**
     * Do not preslash all slashes (parent behavior), but only those:
     *
     * - followed by "\"
     * - by "'"
     * - or the end of the string
     *
     * Prevents `Vendor\Class` => `Vendor\\Class`.
     */
    protected function pSingleQuotedString(string $string): string
    {
        return '\'' . preg_replace("/'|\\\\(?=[\\\\']|$)/", '\\\\$0', $string) . '\'';
    }
}
