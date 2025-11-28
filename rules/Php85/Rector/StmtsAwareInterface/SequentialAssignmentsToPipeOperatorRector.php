<?php

declare (strict_types=1);
namespace Rector\Php85\Rector\StmtsAwareInterface;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Pipe;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\VariadicPlaceholder;
use Rector\NodeAnalyzer\ExprAnalyzer;
use Rector\PhpParser\Enum\NodeGroup;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://wiki.php.net/rfc/pipe-operator-v3
 * @see \Rector\Tests\Php85\Rector\StmtsAwareInterface\SequentialAssignmentsToPipeOperatorRector\SequentialAssignmentsToPipeOperatorRectorTest
 */
final class SequentialAssignmentsToPipeOperatorRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private ExprAnalyzer $exprAnalyzer;
    public function __construct(ExprAnalyzer $exprAnalyzer)
    {
        $this->exprAnalyzer = $exprAnalyzer;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Transform sequential assignments to pipe operator syntax', [new CodeSample(<<<'CODE_SAMPLE'
$value = "hello world";
$result1 = function1($value);
$result2 = function2($result1);

$result = function3($result2);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$value = "hello world";

$result = $value
    |> function1(...)
    |> function2(...)
    |> function3(...);
CODE_SAMPLE
)]);
    }
    public function getNodeTypes(): array
    {
        return NodeGroup::STMTS_AWARE;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::PIPE_OPERATOER;
    }
    /**
     * @param StmtsAware $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->stmts === null) {
            return null;
        }
        $hasChanged = \false;
        $statements = $node->stmts;
        $totalStatements = count($statements) - 1;
        for ($i = 0; $i < $totalStatements; ++$i) {
            $chain = $this->findAssignmentChain($statements, $i);
            if ($chain && count($chain) >= 2) {
                $this->processAssignmentChain($node, $chain, $i);
                $hasChanged = \true;
                // Skip processed statements
                $i += count($chain) - 1;
            }
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    /**
     * @param array<int, Stmt> $statements
     * @return array<int, array{stmt: Stmt, assign: Expr, funcCall: Expr\FuncCall}>|null
     */
    private function findAssignmentChain(array $statements, int $startIndex): ?array
    {
        $chain = [];
        $currentIndex = $startIndex;
        $totalStatements = count($statements);
        while ($currentIndex < $totalStatements) {
            $stmt = $statements[$currentIndex];
            if (!$stmt instanceof Expression) {
                break;
            }
            $expr = $stmt->expr;
            if (!$expr instanceof Assign) {
                return null;
            }
            // Check if this is a simple function call with one argument
            if (!$expr->expr instanceof FuncCall) {
                return null;
            }
            $funcCall = $expr->expr;
            if (count($funcCall->args) !== 1) {
                return null;
            }
            $arg = $funcCall->args[0];
            if (!$arg instanceof Arg) {
                return null;
            }
            if ($currentIndex === $startIndex) {
                // First in chain - must be a variable or simple value
                if (!$arg->value instanceof Variable && !$this->exprAnalyzer->isDynamicExpr($arg->value)) {
                    return null;
                }
                $chain[] = ['stmt' => $stmt, 'assign' => $expr, 'funcCall' => $funcCall];
            } else {
                // Subsequent in chain - must use previous assignment's variable
                $previousAssign = $chain[count($chain) - 1]['assign'];
                $previousVarName = $this->getName($previousAssign->var);
                if (!$arg->value instanceof Variable || $this->getName($arg->value) !== $previousVarName) {
                    break;
                }
                $chain[] = ['stmt' => $stmt, 'assign' => $expr, 'funcCall' => $funcCall];
            }
            ++$currentIndex;
        }
        return $chain;
    }
    /**
     * @param StmtsAware $stmtsAware
     * @param array<int, array{stmt: Stmt, assign: Expr, funcCall: Expr\FuncCall}> $chain
     */
    private function processAssignmentChain(Node $stmtsAware, array $chain, int $startIndex): void
    {
        if ($stmtsAware->stmts === null) {
            return;
        }
        $lastAssignment = $chain[count($chain) - 1]['assign'];
        // Get the initial value from the first function call's argument
        $firstFuncCall = $chain[0]['funcCall'];
        if (!$firstFuncCall instanceof FuncCall) {
            return;
        }
        $firstArg = $firstFuncCall->args[0];
        if (!$firstArg instanceof Arg) {
            return;
        }
        $initialValue = $firstArg->value;
        // Build the pipe chain
        $pipeExpression = $initialValue;
        foreach ($chain as $chainItem) {
            $funcCall = $chainItem['funcCall'];
            $placeholderCall = $this->createPlaceholderCall($funcCall);
            $pipeExpression = new Pipe($pipeExpression, $placeholderCall);
        }
        if (!$lastAssignment instanceof Assign) {
            return;
        }
        // Create the final assignment
        $assign = new Assign($lastAssignment->var, $pipeExpression);
        $finalExpression = new Expression($assign);
        // Replace the statements
        $endIndex = $startIndex + count($chain) - 1;
        // Remove all intermediate statements and replace with the final pipe expression
        for ($i = $startIndex; $i <= $endIndex; ++$i) {
            if ($i === $startIndex) {
                $stmtsAware->stmts[$i] = $finalExpression;
            } else {
                unset($stmtsAware->stmts[$i]);
            }
        }
        // Reindex the array
        $stmtsAware->stmts = array_values($stmtsAware->stmts);
    }
    private function createPlaceholderCall(FuncCall $funcCall): FuncCall
    {
        return new FuncCall($funcCall->name, [new VariadicPlaceholder()]);
    }
}
