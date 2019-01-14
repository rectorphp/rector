<?php declare(strict_types=1);

namespace Rector\PHPUnit\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\TryCatch;
use Rector\Rector\AbstractPHPUnitRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class TryCatchToExpectExceptionRector extends AbstractPHPUnitRector
{
    /**
     * @var Expression[]
     */
    private $newExpressions = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns try/catch to expectException() call', [
            new CodeSample(
                <<<'CODE_SAMPLE'
try {
	$someService->run();
} catch (Throwable $exception) {
    $this->assertInstanceOf(RuntimeException::class, $e);
    $this->assertContains('There was an error executing the following script', $e->getMessage());
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
$this->expectException(RuntimeException::class);
$this->expectExceptionMessage('There was an error executing the following script');
$someService->run();
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isInTestClass($node)) {
            return null;
        }

        if (! $node->stmts) {
            return null;
        }

        $proccesed = [];
        foreach ($node->stmts as $key => $stmt) {
            if (! $stmt instanceof TryCatch) {
                continue;
            }

            $proccesed = $this->processTryCatch($stmt);
            if ($proccesed === null) {
                continue;
            }

            unset($node->stmts[$key]);
        }

        $node->stmts = array_merge($node->stmts, $proccesed);

        return $node;
    }

    /**
     * @return Expression[]|null
     */
    private function processTryCatch(TryCatch $tryCatchNode): ?array
    {
        if (count($tryCatchNode->catches) !== 1) {
            return null;
        }

        $this->newExpressions = [];

        $exceptionVariable = $tryCatchNode->catches[0]->var;

        // we look for:
        // - instance of $exceptionVariableName
        // - assert same string to $exceptionVariableName->getMessage()
        // - assert same string to $exceptionVariableName->getCode()
        foreach ($tryCatchNode->catches[0]->stmts as $catchedStmt) {
            // not a match
            if (! $catchedStmt instanceof Expression) {
                return null;
            }

            if (! $catchedStmt->expr instanceof MethodCall) {
                continue;
            }

            $methodCallNode = $catchedStmt->expr;

            $this->processAssertInstanceOf($methodCallNode, $exceptionVariable);
            $this->processExceptionMessage($methodCallNode, $exceptionVariable);
            $this->processExceptionCode($methodCallNode, $exceptionVariable);
            $this->processExceptionMessageContains($methodCallNode, $exceptionVariable);
        }

        // return all statements
        foreach ($tryCatchNode->stmts as $stmt) {
            if (! $stmt instanceof Expression) {
                return null;
            }

            $this->newExpressions[] = $stmt;
        }

        return $this->newExpressions;
    }

    private function processAssertInstanceOf(MethodCall $methodCallNode, Variable $exceptionVariableNode): void
    {
        if (! $this->isName($methodCallNode->var, 'this')) {
            return;
        }

        if (! $this->isName($methodCallNode, 'assertInstanceOf')) {
            return;
        }

        /** @var MethodCall $methodCallNode */
        $argumentVariableName = $this->getName($methodCallNode->args[1]->value);
        if ($argumentVariableName === null) {
            return;
        }

        // is na exception variable
        if (! $this->isName($exceptionVariableNode, $argumentVariableName)) {
            return;
        }

        $this->newExpressions[] = new Expression(new MethodCall($methodCallNode->var, 'expectException', [
            $methodCallNode->args[0],
        ]));
    }

    private function processExceptionMessage(MethodCall $methodCallNode, Variable $exceptionVariable): void
    {
        if (! $this->isName($methodCallNode->var, 'this')) {
            return;
        }

        if (! $this->isNames($methodCallNode, ['assertSame', 'assertEquals'])) {
            return;
        }

        $secondArgument = $methodCallNode->args[1]->value;
        if (! $secondArgument instanceof MethodCall) {
            return;
        }

        if (! $this->areNodesEqual($secondArgument->var, $exceptionVariable)) {
            return;
        }

        if (! $this->isName($secondArgument, 'getMessage')) {
            return;
        }

        $this->newExpressions[] = $this->renameMethodCallAndKeepFirstArgument(
            $methodCallNode,
            'expectExceptionMessage'
        );
    }

    private function processExceptionCode(MethodCall $methodCallNode, Variable $exceptionVariable): void
    {
        if (! $this->isName($methodCallNode->var, 'this')) {
            return;
        }

        if (! $this->isNames($methodCallNode, ['assertSame', 'assertEquals'])) {
            return;
        }

        $secondArgument = $methodCallNode->args[1]->value;
        if (! $secondArgument instanceof MethodCall) {
            return;
        }

        // looking for "$exception->getMessage()"
        if (! $this->areNamesEqual($secondArgument->var, $exceptionVariable)) {
            return;
        }

        if (! $this->isName($secondArgument, 'getCode')) {
            return;
        }

        $this->newExpressions[] = $this->renameMethodCallAndKeepFirstArgument($methodCallNode, 'expectExceptionCode');
    }

    private function processExceptionMessageContains(MethodCall $methodCallNode, Variable $exceptionVariable): void
    {
        if (! $this->isName($methodCallNode->var, 'this')) {
            return;
        }

        if (! $this->isName($methodCallNode, 'assertContains')) {
            return;
        }

        $secondArgument = $methodCallNode->args[1]->value;
        if (! $secondArgument instanceof MethodCall) {
            return;
        }

        // looking for "$exception->getMessage()"
        if (! $this->areNodesEqual($secondArgument->var, $exceptionVariable)) {
            return;
        }

        if (! $this->isName($secondArgument, 'getMessage')) {
            return;
        }

        $expression = $this->renameMethodCallAndKeepFirstArgument($methodCallNode, 'expectExceptionMessageRegExp');
        /** @var MethodCall $methodCallNode */
        $methodCallNode = $expression->expr;
        // put regex between "#...#" to create match
        if ($methodCallNode->args[0]->value instanceof String_) {
            /** @var String_ $oldStringNode */
            $oldStringNode = $methodCallNode->args[0]->value;
            $methodCallNode->args[0]->value = new String_('#' . preg_quote($oldStringNode->value) . '#');
        }

        $this->newExpressions[] = $expression;
    }

    private function renameMethodCallAndKeepFirstArgument(MethodCall $methodCallNode, string $methodName): Expression
    {
        $methodCallNode->name = new Identifier($methodName);
        foreach (array_keys($methodCallNode->args) as $i) {
            // keep first arg
            if ($i === 0) {
                continue;
            }

            unset($methodCallNode->args[$i]);
        }

        return new Expression($methodCallNode);
    }
}
