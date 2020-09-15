<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Nop;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\CodingStyle\Tests\Rector\ClassMethod\NewlineBeforeNewAssignSetRector\NewlineBeforeNewAssignSetRectorTest
 */
final class NewlineBeforeNewAssignSetRector extends AbstractRector
{
    /**
     * @var string|null
     */
    private $previousStmtVariableName;

    /**
     * @var string|null
     */
    private $previousPreviousStmtVariableName;

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Add extra space before new assign set', [
            new CodeSample(
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        $value = new Value;
        $value->setValue(5);
        $value2 = new Value;
        $value2->setValue(1);
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        $value = new Value;
        $value->setValue(5);

        $value2 = new Value;
        $value2->setValue(1);
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class, Function_::class, Closure::class];
    }

    /**
     * @param ClassMethod|Function_|Closure $node
     */
    public function refactor(Node $node): ?Node
    {
        $this->reset();

        $hasChanged = false;

        foreach ((array) $node->stmts as $key => $stmt) {
            $currentStmtVariableName = $this->resolveCurrentStmtVariableName($stmt);

            if ($this->shouldAddEmptyLine($currentStmtVariableName, $node, $key)) {
                $hasChanged = true;
                // insert newline before
                array_splice($node->stmts, $key, 0, [new Nop()]);
            }

            $this->previousPreviousStmtVariableName = $this->previousStmtVariableName;
            $this->previousStmtVariableName = $currentStmtVariableName;
        }

        return $hasChanged ? $node : null;
    }

    private function reset(): void
    {
        $this->previousStmtVariableName = null;
        $this->previousPreviousStmtVariableName = null;
    }

    private function resolveCurrentStmtVariableName(Node $node): ?string
    {
        $node = $this->unwrapExpression($node);

        if ($node instanceof Assign || $node instanceof MethodCall) {
            if ($this->shouldSkipLeftVariable($node)) {
                return null;
            }

            if (! $node->var instanceof MethodCall && ! $node->var instanceof StaticCall) {
                return $this->getName($node->var);
            }
        }

        return null;
    }

    /**
     * @param ClassMethod|Function_|Closure $node
     */
    private function shouldAddEmptyLine(?string $currentStmtVariableName, Node $node, int $key): bool
    {
        if (! $this->isNewVariableThanBefore($currentStmtVariableName)) {
            return false;
        }

        // this is already empty line before
        return ! $this->isPreceededByEmptyLine($node, $key);
    }

    private function unwrapExpression(Node $node): Node
    {
        if ($node instanceof Expression) {
            return $node->expr;
        }

        return $node;
    }

    /**
     * @param Assign|MethodCall $node
     */
    private function shouldSkipLeftVariable(Node $node): bool
    {
        // local method call
        return $this->isVariableName($node->var, 'this');
    }

    private function isNewVariableThanBefore(?string $currentStmtVariableName): bool
    {
        if ($this->previousPreviousStmtVariableName === null) {
            return false;
        }

        if ($this->previousStmtVariableName === null) {
            return false;
        }

        if ($currentStmtVariableName === null) {
            return false;
        }

        if ($this->previousStmtVariableName !== $this->previousPreviousStmtVariableName) {
            return false;
        }

        return $this->previousStmtVariableName !== $currentStmtVariableName;
    }

    /**
     * @param ClassMethod|Function_|Closure $node
     */
    private function isPreceededByEmptyLine(Node $node, int $key): bool
    {
        if ($node->stmts === null) {
            return false;
        }

        $previousNode = $node->stmts[$key - 1];
        $currentNode = $node->stmts[$key];

        return abs($currentNode->getLine() - $previousNode->getLine()) >= 2;
    }
}
