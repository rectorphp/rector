<?php declare(strict_types=1);

namespace Rector\PhpParser\Printer;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\EncapsedStringPart;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use PhpParser\PrettyPrinter\Standard;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\Php\Rector\Property\TypedPropertyRector;
use function Safe\sprintf;
use Symplify\PackageBuilder\Reflection\PrivatesCaller;

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

        // print return type double colon right after the bracket "function(): string"
        $this->initializeInsertionMap();
        $this->insertionMap['Stmt_ClassMethod->returnType'] = [')', ': ', null];
        $this->insertionMap['Stmt_Function->returnType'] = [')', ': ', null];
    }

    /**
     * @param Node|Node[]|null $node
     */
    public function print($node): string
    {
        if ($node === null) {
            $node = [];
        }

        if ($node instanceof EncapsedStringPart) {
            return 'UNABLE_TO_PRINT_ENCAPSED_STRING';
        }

        if (! is_array($node)) {
            $node = [$node];
        }

        return $this->prettyPrint($node);
    }

    /**
     * @param Node|Node[] $firstNode
     * @param Node|Node[] $secondNode
     */
    public function areNodesEqual($firstNode, $secondNode): bool
    {
        return $this->print($firstNode) === $this->print($secondNode);
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
        return '\'' . preg_replace('#'|\\\\(?=[\\\\']|$)#', '\\\\$0', $string) . '\'';
    }

    /**
     * Add space after "use ("
     */
    protected function pExpr_Closure(Closure $node): string
    {
        return Strings::replace(parent::pExpr_Closure($node), '#( use)\(#', '$1 (');
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
        if ($type instanceof Node) {
            $type = $this->p($type);
        }

        return $node->flags === 0 ? 'var ' : $this->pModifiers($node->flags) .
            ($type ? $type . ' ' : '') .
            $this->pCommaSeparated($node->props) .
            ';';
    }

    /**
     * Print ??= since PHP 7.4
     */
    protected function pExpr_BinaryOp_Coalesce(Coalesce $node): string
    {
        if (! $node->getAttribute('null_coalesce')) {
            return parent::pExpr_BinaryOp_Coalesce($node);
        }

        return $this->pInfixOp(Coalesce::class, $node->left, ' ??= ', $node->right);
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

    /**
     * Allows PHP 7.3 trailing comma in multiline args
     * @see printArgs() bellow
     */
    protected function pExpr_FuncCall(FuncCall $node): string
    {
        return $this->pCallLhs($node->name) . '(' . $this->printArgs($node) . ')';
    }

    protected function pExpr_MethodCall(MethodCall $node): string
    {
        return $this->pDereferenceLhs($node->var)
            . '->'
            . $this->pObjectProperty($node->name)
            . '('
            . $this->printArgs($node)
            . ')';
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
     * Workaround to https://github.com/nikic/PHP-Parser/issues/554
     *
     * @param bool $parentFormatPreserved Whether parent node has preserved formatting
     */
    protected function p(Node $node, $parentFormatPreserved = false): string
    {
        if ($node instanceof New_ && $node->class instanceof Class_) {
            if ($node->class->name instanceof Identifier) {
                $className = $node->class->name->toString();
                if (Strings::startsWith($className, 'AnonymousClass')) {
                    /** @var Class_ $originalNode */
                    $originalNode = $node->class->getAttribute(Attribute::ORIGINAL_NODE);
                    $originalNode->name = null;

                    $node->class->setAttribute(Attribute::ORIGINAL_NODE, $originalNode);
                    $node->class->name = null;
                }
            }
        }

        return parent::p($node, $parentFormatPreserved);
    }

    /**
     * Fixes escaping of regular patterns
     */
    protected function pScalar_String(String_ $node): string
    {
        $kind = $node->getAttribute('kind', String_::KIND_SINGLE_QUOTED);
        if ($kind === String_::KIND_DOUBLE_QUOTED && $node->getAttribute('is_regular_pattern')) {
            return '"' . $node->value . '"';
        }

        return parent::pScalar_String($node);
    }

    /**
     * "...$params) : ReturnType"
     * ↓
     * "...$params): ReturnType"
     */
    protected function pStmt_ClassMethod(ClassMethod $node): string
    {
        return $this->pModifiers($node->flags)
            . 'function ' . ($node->byRef ? '&' : '') . $node->name
            . '(' . $this->pCommaSeparated($node->params) . ')'
            . ($node->returnType !== null ? ': ' . $this->p($node->returnType) : '')
            . ($node->stmts !== null ? $this->nl . '{' . $this->pStmts($node->stmts) . $this->nl . '}' : ';');
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
