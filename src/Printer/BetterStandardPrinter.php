<?php declare(strict_types=1);

namespace Rector\Printer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use PhpParser\PrettyPrinter\Standard;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\Php\Rector\Property\TypedPropertyRector;
use Symplify\PackageBuilder\Reflection\PrivatesCaller;
use function Safe\sprintf;

final class BetterStandardPrinter extends Standard
{
    /**
     * @var PrivatesCaller
     */
    private $privatesCaller;

    /**
     * @param mixed[] $options
     */
    public function __construct(PrivatesCaller $privatesCaller, array $options = [])
    {
        parent::__construct($options);
        $this->privatesCaller = $privatesCaller;
    }

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
    protected function pExpr_Yield(Yield_ $node): string
    {
        if ($node->value === null) {
            return 'yield';
        }

        $parentNode = $node->getAttribute(Attribute::PARENT_NODE);
        $shouldAddBrackets = $parentNode instanceof Expression;

        return sprintf(
            '%syield %s%s%s',
            $shouldAddBrackets ? '(' : '',
            $node->key !== null ? $this->p($node->key) . ' => ' : '',
            $this->p($node->value),
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

    /**
     * Print arrays in short [] by default,
     * to prevent manual explicit array shortening.
     */
    protected function pExpr_Array(Array_ $node): string
    {
        if (! $node->hasAttribute('kind')) {
            $node->setAttribute('kind', Array_::KIND_SHORT);
        }

        return parent::pExpr_Array($node);
    }

    protected function pExpr_FuncCall(FuncCall $node): string
    {
        return $this->pCallLhs($node->name) . '(' . $this->printArgs($node) . ')';
    }

    protected function pExpr_MethodCall(MethodCall $node): string
    {
        return $this->pDereferenceLhs($node->var) . '->' . $this->pObjectProperty($node->name)
            . '(' . $this->printArgs($node) . ')';
    }

    protected function pExpr_StaticCall(StaticCall $node): string
    {
        return $this->pDereferenceLhs($node->class) . '::'
            . ($node->name instanceof Expr
                ? ($node->name instanceof Variable
                    ? $this->p($node->name)
                    : '{' . $this->p($node->name) . '}')
                : $node->name)
            . '(' . $this->printArgs($node) . ')';
    }

    /**
     * Allows PHP 7.3 trailing comma in multiline args
     *
     * @param FuncCall|MethodCall|StaticCall $node
     */
    private function printArgs(Node $node): string
    {
        return $this->privatesCaller->callPrivateMethod(
            $this,
            'pMaybeMultiline',
            $node->args,
            $node->getAttribute('trailingComma', false)
        );
    }
}
