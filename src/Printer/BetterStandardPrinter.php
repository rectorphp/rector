<?php declare(strict_types=1);

namespace Rector\Printer;

use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\PrettyPrinter\Standard;
use Rector\Node\Attribute;

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

    /**
     * Do not add () on Expressions
     * @see https://github.com/rectorphp/rector/pull/401#discussion_r181487199
     */
    protected function pExpr_Yield(Yield_ $yieldNode)
    {
        if ($yieldNode->value === null) {
            return 'yield';
        } else {
            $parentNode = $yieldNode->getAttribute(Attribute::PARENT_NODE);
            $yieldContent = 'yield '
                . ($yieldNode->key !== null ? $this->p($yieldNode->key) . ' => ' : '')
                . $this->p($yieldNode->value);

            if (! $parentNode instanceof Expression) {
                return $yieldContent;
            }

            return '(' . $yieldContent . ')';
        }
    }
}
