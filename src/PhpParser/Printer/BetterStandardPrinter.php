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
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\Scalar\EncapsedStringPart;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\PrettyPrinter\Standard;
use Rector\NodeTypeResolver\Node\Attribute;
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
        $this->insertionMap['Stmt_ClassMethod->returnType'] = [')', false, ': ', null];
        $this->insertionMap['Stmt_Function->returnType'] = [')', false, ': ', null];
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
     * @param Node|Node[]|null $firstNode
     * @param Node|Node[]|null $secondNode
     */
    public function areNodesEqual($firstNode, $secondNode): bool
    {
        return $this->print($firstNode) === $this->print($secondNode);
    }

    /**
     * @param mixed[] $nodes
     * @param mixed[] $origNodes
     * @param int|null $fixup
     * @param string|null $insertStr
     */
    public function pArray(
        array $nodes,
        array $origNodes,
        int &$pos,
        int $indentAdjustment,
        string $subNodeName,
        $fixup,
        $insertStr
    ): ?string {
        // reindex positions for printer
        $nodes = array_values($nodes);

        return parent::pArray($nodes, $origNodes, $pos, $indentAdjustment, $subNodeName, $fixup, $insertStr);
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
        return '\'' . Strings::replace($string, "#'|\\\\(?=[\\\\']|$)#", '\\\\$0') . '\'';
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

    /**
     * Allows PHP 7.3 trailing comma in multiline args
     * @see printArgs() bellow
     */
    protected function pExpr_MethodCall(MethodCall $node): string
    {
        return $this->pDereferenceLhs($node->var)
            . '->'
            . $this->pObjectProperty($node->name)
            . '('
            . $this->printArgs($node)
            . ')';
    }

    /**
     * Allows PHP 7.3 trailing comma in multiline args
     * @see printArgs() bellow
     */
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
