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

        $node->stmts = array_merge($node->stmts, (array) $proccesed);

        return $node;
    }

    /**
     * @return Expression[]|null
     */
    private function processTryCatch(TryCatch $tryCatch): ?array
    {
        if (count($tryCatch->catches) !== 1) {
            return null;
        }

        $this->newExpressions = [];

        $exceptionVariable = $tryCatch->catches[0]->var;

        // we look for:
        // - instance of $exceptionVariableName
        // - assert same string to $exceptionVariableName->getMessage()
        // - assert same string to $exceptionVariableName->getCode()
        foreach ($tryCatch->catches[0]->stmts as $catchedStmt) {
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
        foreach ($tryCatch->stmts as $stmt) {
            if (! $stmt instanceof Expression) {
                return null;
            }

            $this->newExpressions[] = $stmt;
        }

        return $this->newExpressions;
    }

    private function processAssertInstanceOf(MethodCall $methodCall, Variable $variable): void
    {
        if (! $this->isName($methodCall->var, 'this')) {
            return;
        }

        if (! $this->isName($methodCall, 'assertInstanceOf')) {
            return;
        }

        /** @var MethodCall $methodCall */
        $argumentVariableName = $this->getName($methodCall->args[1]->value);
        if ($argumentVariableName === null) {
            return;
        }

        // is na exception variable
        if (! $this->isName($variable, $argumentVariableName)) {
            return;
        }

        $this->newExpressions[] = new Expression(new MethodCall($methodCall->var, 'expectException', [
            $methodCall->args[0],
        ]));
    }

    private function processExceptionMessage(MethodCall $methodCall, Variable $exceptionVariable): void
    {
        if (! $this->isName($methodCall->var, 'this')) {
            return;
        }

        if (! $this->isNames($methodCall, ['assertSame', 'assertEquals'])) {
            return;
        }

        $secondArgument = $methodCall->args[1]->value;
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
            $methodCall,
            'expectExceptionMessage'
        );
    }

    private function processExceptionCode(MethodCall $methodCall, Variable $exceptionVariable): void
    {
        if (! $this->isName($methodCall->var, 'this')) {
            return;
        }

        if (! $this->isNames($methodCall, ['assertSame', 'assertEquals'])) {
            return;
        }

        $secondArgument = $methodCall->args[1]->value;
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

        $this->newExpressions[] = $this->renameMethodCallAndKeepFirstArgument($methodCall, 'expectExceptionCode');
    }

    private function processExceptionMessageContains(MethodCall $methodCall, Variable $exceptionVariable): void
    {
        if (! $this->isName($methodCall->var, 'this')) {
            return;
        }

        if (! $this->isName($methodCall, 'assertContains')) {
            return;
        }

        $secondArgument = $methodCall->args[1]->value;
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

        $expression = $this->renameMethodCallAndKeepFirstArgument($methodCall, 'expectExceptionMessageRegExp');
        /** @var MethodCall $methodCall */
        $methodCall = $expression->expr;
        // put regex between "#...#" to create match
        if ($methodCall->args[0]->value instanceof String_) {
            /** @var String_ $oldString */
            $oldString = $methodCall->args[0]->value;
            $methodCall->args[0]->value = new String_('#' . preg_quote($oldString->value) . '#');
        }

        $this->newExpressions[] = $expression;
    }

    private function renameMethodCallAndKeepFirstArgument(MethodCall $methodCall, string $methodName): Expression
    {
        $methodCall->name = new Identifier($methodName);
        foreach (array_keys($methodCall->args) as $i) {
            // keep first arg
            if ($i === 0) {
                continue;
            }

            unset($methodCall->args[$i]);
        }

        return new Expression($methodCall);
    }
}
