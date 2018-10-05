<?php declare(strict_types=1);

namespace Rector\Printer;

use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use PhpParser\PrettyPrinter\Standard;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\Php\Rector\Property\TypedPropertyRector;
use function Safe\sprintf;

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
     * Do not add "()" on Expressions
     * @see https://github.com/rectorphp/rector/pull/401#discussion_r181487199
     */
    protected function pExpr_Yield(Yield_ $yieldNode): string
    {
        if ($yieldNode->value === null) {
            return 'yield';
        }

        $parentNode = $yieldNode->getAttribute(Attribute::PARENT_NODE);
        $shouldAddBrackets = $parentNode instanceof Expression;

        return sprintf(
            '%syield %s%s%s',
            $shouldAddBrackets ? '(' : '',
            $yieldNode->key !== null ? $this->p($yieldNode->key) . ' => ' : '',
            $this->p($yieldNode->value),
            $shouldAddBrackets ? ')' : ''
        );
    }

    /**
     * Print property with PHP 7.4 type
     */
    protected function pStmt_Property(Property $node): string
    {
        $type = $node->getAttribute(TypedPropertyRector::PHP74_PROPERTY_TYPE);

        return $node->flags === 0 ? 'var ' : $this->pModifiers($node->flags) .
            ($type ? $type . ' ' : '') .
            $this->pCommaSeparated($node->props) .
            ';';
    }
}
