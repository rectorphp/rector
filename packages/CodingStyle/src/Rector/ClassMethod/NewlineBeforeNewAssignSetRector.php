<?php declare(strict_types=1);

namespace Rector\CodingStyle\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Nop;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\CodingStyle\Tests\Rector\ClassMethod\NewlineBeforeNewAssignSetRector\NewlineBeforeNewAssignSetRectorTest
 */
final class NewlineBeforeNewAssignSetRector extends AbstractRector
{
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
        return [Node\Stmt\ClassMethod::class, Node\Stmt\Function_::class, Node\Expr\Closure::class];
    }

    /**
     * @param Node\Stmt\ClassMethod|Node\Stmt\Function_|Node\Expr\Closure $node
     */
    public function refactor(Node $node): ?Node
    {
        $previousStmtVariableName = null;

        if ($node->stmts === null) {
            return null;
        }

        foreach ($node->stmts as $key => $stmt) {
            $currentStmtVariableName = null;

            if ($stmt instanceof Expression) {
                $stmt = $stmt->expr;
            }

            if ($stmt instanceof Assign || $stmt instanceof MethodCall) {
                if ($stmt->var instanceof Variable) {
                    $currentStmtVariableName = $this->getName($stmt->var);
                }
            }

            if ($this->isNewVariableThanBefore($previousStmtVariableName, $currentStmtVariableName)) {
                // insert newline before
                array_splice($node->stmts, $key, 0, [new Nop()]);
            }

            $previousStmtVariableName = $currentStmtVariableName;
        }

        return $node;
    }

    private function isNewVariableThanBefore(?string $previousStmtVariableName, ?string $currentStmtVariableName): bool
    {
        if ($previousStmtVariableName === null) {
            return false;
        }

        if ($currentStmtVariableName === null) {
            return false;
        }

        return $previousStmtVariableName !== $currentStmtVariableName;
    }
}
