<?php declare(strict_types=1);

namespace Rector\Printer;

use PhpParser\PrettyPrinter\Standard;

final class BetterStandardPrinter extends Standard
{
    protected function pSingleQuotedString(string $string): string
    {
        return '\'' . addcslashes($string, '\'\\') . '\'';
    }
}
