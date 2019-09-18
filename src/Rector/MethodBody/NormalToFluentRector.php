<?php declare(strict_types=1);

namespace Rector\Rector\MethodBody;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Tests\Rector\MethodBody\NormalToFluentRector\NormalToFluentRectorTest
 */
final class NormalToFluentRector extends AbstractRector
{
    /**
     * @var string[][]
     */
    private $fluentMethodsByType = [];

    /**
     * @var MethodCall[]
     */
    private $collectedMethodCalls = [];

    /**
     * @param string[][] $fluentMethodsByType
     */
    public function __construct(array $fluentMethodsByType = [])
    {
        $this->fluentMethodsByType = $fluentMethodsByType;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns fluent interface calls to classic ones.', [
            new ConfiguredCodeSample(
                <<<'PHP'
$someObject = new SomeClass();
$someObject->someFunction();
$someObject->otherFunction();
PHP
                ,
                <<<'PHP'
$someObject = new SomeClass();
$someObject->someFunction()
    ->otherFunction();
PHP
                ,
                [
                    'SomeClass' => ['someFunction', 'otherFunction'],
                ]
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
        // process only existing statements
        if ($node->stmts === null) {
            return null;
        }

        $classMethodStatementCount = count($node->stmts);

        // iterate from bottom to up, so we can merge
        for ($i = $classMethodStatementCount - 1; $i >= 0; --$i) {
            $stmt = $node->stmts[$i];

            // we look only for 2+ stmts
            if (! isset($node->stmts[$i - 1])) {
                continue;
            }

            // we look for 2 methods calls in a row
            if (! $stmt instanceof Expression) {
                continue;
            }

            $prevStmt = $node->stmts[$i - 1];
            if (! $prevStmt instanceof Expression) {
                continue;
            }

            // here are 2 method calls statements in a row, while current one is first one
            if (! $this->isBothMethodCallMatch($stmt, $prevStmt)) {
                if (count($this->collectedMethodCalls) >= 2) {
                    $this->fluentizeCollectedMethodCalls($node);
                }

                // reset for new type
                $this->collectedMethodCalls = [];

                continue;
            }

            // add all matching fluent calls
            /** @var MethodCall $currentMethodCall */
            $currentMethodCall = $stmt->expr;
            $this->collectedMethodCalls[$i] = $currentMethodCall;

            /** @var MethodCall $previousMethodCall */
            $previousMethodCall = $prevStmt->expr;
            $this->collectedMethodCalls[$i - 1] = $previousMethodCall;
        }

        return $node;
    }

    private function isBothMethodCallMatch(Expression $firstStmt, Expression $secondStmt): bool
    {
        if (! $firstStmt->expr instanceof MethodCall) {
            return false;
        }

        if (! $secondStmt->expr instanceof MethodCall) {
            return false;
        }

        $firstMethodCallMatch = $this->matchMethodCall($firstStmt->expr);
        if ($firstMethodCallMatch === null) {
            return false;
        }

        $secondMethodCallMatch = $this->matchMethodCall($secondStmt->expr);
        if ($secondMethodCallMatch === null) {
            return false;
        }

        // is the same type
        return $firstMethodCallMatch === $secondMethodCallMatch;
    }

    private function fluentizeCollectedMethodCalls(ClassMethod $classMethod): void
    {
        $i = 0;
        $fluentMethodCallIndex = null;
        $methodCallsToAdd = [];
        foreach ($this->collectedMethodCalls as $statementIndex => $methodCall) {
            if ($i === 0) {
                // first method call, add it
                $fluentMethodCallIndex = $statementIndex;
            } else {
                $methodCallsToAdd[] = $methodCall;
                // next method calls, unset them
                unset($classMethod->stmts[$statementIndex]);
            }

            ++$i;
        }

        /** @var MethodCall $fluentMethodCall */
        $fluentMethodCall = $classMethod->stmts[$fluentMethodCallIndex]->expr;

        // they are added in reversed direction
        $methodCallsToAdd = array_reverse($methodCallsToAdd);

        foreach ($methodCallsToAdd as $methodCallToAdd) {
            $fluentMethodCall->var = new MethodCall( // make var a parent method call
                $fluentMethodCall->var,
                $methodCallToAdd->name,
                $methodCallToAdd->args
            );
        }
    }

    private function matchMethodCall(MethodCall $methodCall): ?string
    {
        foreach ($this->fluentMethodsByType as $type => $methodNames) {
            if (! $this->isObjectType($methodCall, $type)) {
                continue;
            }

            if ($this->isNames($methodCall, $methodNames)) {
                return $type;
            }
        }

        return null;
    }
}
