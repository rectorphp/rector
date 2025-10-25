<?php

declare (strict_types=1);
namespace Rector\DowngradePhp85\Rector\StmtsAwareInterface;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Pipe;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\NodeFactory\NamedVariableFactory;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://wiki.php.net/rfc/pipe-operator-v3
 * @see \Rector\Tests\DowngradePhp85\Rector\StmtsAwareInterface\DowngradePipeOperatorRector\DowngradePipeOperatorRectorTest
 */
final class DowngradePipeOperatorRector extends AbstractRector
{
    /**
     * @readonly
     */
    private NamedVariableFactory $namedVariableFactory;
    public function __construct(NamedVariableFactory $namedVariableFactory)
    {
        $this->namedVariableFactory = $namedVariableFactory;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Downgrade pipe operator |> to PHP < 8.5 compatible code', [new CodeSample(<<<'CODE_SAMPLE'
$value = "hello world";
$result = $value
    |> function3(...)
    |> function2(...)
    |> function1(...);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$value = "hello world";
$result1 = function3($value);
$result2 = function2($result1);
$result = function1($result2);
CODE_SAMPLE
), new CodeSample(<<<'CODE_SAMPLE'
$result = strtoupper("Hello World") |> trim(...);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$result = trim(strtoupper("Hello World"));
CODE_SAMPLE
)]);
    }
    public function getNodeTypes(): array
    {
        return [StmtsAwareInterface::class];
    }
    /**
     * @param StmtsAwareInterface $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->stmts === null) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->stmts as $key => $stmt) {
            if (!$stmt instanceof Expression) {
                continue;
            }
            $pipeNode = $this->findPipeNode($stmt->expr);
            if (!$pipeNode instanceof Pipe) {
                continue;
            }
            $newStmts = $this->processPipeOperation($pipeNode, $stmt);
            if ($newStmts === null) {
                continue;
            }
            // Replace the current statement with new statements
            array_splice($node->stmts, $key, 1, $newStmts);
            $hasChanged = \true;
        }
        return $hasChanged ? $node : null;
    }
    private function findPipeNode(Node $node): ?Pipe
    {
        if ($node instanceof Pipe) {
            return $node;
        }
        if ($node instanceof Assign && $node->expr instanceof Pipe) {
            return $node->expr;
        }
        return null;
    }
    /**
     * @return Expression[]|null
     */
    private function processPipeOperation(Pipe $pipe, Expression $originalExpression): ?array
    {
        $pipeChain = $this->collectPipeChain($pipe);
        if (count($pipeChain) < 2) {
            return null;
        }
        // For simple case: single pipe operation
        if (count($pipeChain) === 2) {
            $replacement = $this->createSimplePipeReplacement($pipeChain[0], $pipeChain[1]);
            if (!$replacement instanceof Node) {
                return null;
            }
            // If the pipe was part of an assignment, maintain the assignment
            if ($originalExpression->expr instanceof Assign) {
                $newAssign = new Assign($originalExpression->expr->var, $replacement);
                return [new Expression($newAssign)];
            }
            return [new Expression($replacement)];
        }
        // For multiple pipe operations
        return $this->createMultiplePipeReplacement($pipeChain, $originalExpression);
    }
    /**
     * @return array<Node>
     */
    private function collectPipeChain(Pipe $pipe): array
    {
        $chain = [];
        $current = $pipe;
        while ($current instanceof Pipe) {
            $chain[] = $current->right;
            $current = $current->left;
        }
        $chain[] = $current;
        return array_reverse($chain);
    }
    private function createSimplePipeReplacement(Node $input, Node $function): ?Node
    {
        if (!$this->isCallableNode($function)) {
            return null;
        }
        return $this->createFunctionCall($function, [$input]);
    }
    /**
     * @param array<Node> $pipeChain
     * @return Expression[]|null
     */
    private function createMultiplePipeReplacement(array $pipeChain, Expression $originalExpression): ?array
    {
        $input = $pipeChain[0];
        $statements = [];
        // Create all intermediate assignments
        for ($i = 1; $i < count($pipeChain) - 1; ++$i) {
            $function = $pipeChain[$i];
            if (!$this->isCallableNode($function)) {
                return null;
            }
            $tempVar = $this->namedVariableFactory->createVariable('result', $originalExpression);
            $functionCall = $this->createFunctionCall($function, [$input]);
            $assign = new Assign($tempVar, $functionCall);
            $statements[] = new Expression($assign);
            $input = $tempVar;
        }
        // Create the final function call
        $finalFunction = $pipeChain[count($pipeChain) - 1];
        if (!$this->isCallableNode($finalFunction)) {
            return null;
        }
        $node = $this->createFunctionCall($finalFunction, [$input]);
        // If the pipe was part of an assignment, maintain the assignment
        if ($originalExpression->expr instanceof Assign) {
            $newAssign = new Assign($originalExpression->expr->var, $node);
            $statements[] = new Expression($newAssign);
        } else {
            $statements[] = new Expression($node);
        }
        return $statements;
    }
    private function isCallableNode(Node $node): bool
    {
        return $node instanceof FuncCall || $node instanceof Closure || $node instanceof ArrowFunction || $node instanceof Variable || $node instanceof MethodCall || $node instanceof StaticCall;
    }
    /**
     * @param Node[] $arguments
     */
    private function createFunctionCall(Node $node, array $arguments): Node
    {
        if ($node instanceof FuncCall) {
            return new FuncCall($node->name, $this->nodeFactory->createArgs($arguments));
        }
        if ($node instanceof Variable) {
            return new FuncCall($node, $this->nodeFactory->createArgs($arguments));
        }
        if ($node instanceof Closure || $node instanceof ArrowFunction) {
            return new FuncCall($node, $this->nodeFactory->createArgs($arguments));
        }
        if ($node instanceof MethodCall) {
            $node->args = $this->nodeFactory->createArgs($arguments);
            return $node;
        }
        if ($node instanceof StaticCall) {
            $node->args = $this->nodeFactory->createArgs($arguments);
            return $node;
        }
        return new FuncCall($node, $this->nodeFactory->createArgs($arguments));
    }
}
