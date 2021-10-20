<?php

declare (strict_types=1);
namespace Rector\Defluent\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\ObjectType;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\Defluent\ValueObject\NormalToFluent;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20211020\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Defluent\Rector\ClassMethod\NormalToFluentRector\NormalToFluentRectorTest
 */
final class NormalToFluentRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const CALLS_TO_FLUENT = 'calls_to_fluent';
    /**
     * @var NormalToFluent[]
     */
    private $callsToFluent = [];
    /**
     * @var MethodCall[]
     */
    private $collectedMethodCalls = [];
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Turns fluent interface calls to classic ones.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
$someObject = new SomeClass();
$someObject->someFunction();
$someObject->otherFunction();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$someObject = new SomeClass();
$someObject->someFunction()
    ->otherFunction();
CODE_SAMPLE
, [self::CALLS_TO_FLUENT => [new \Rector\Defluent\ValueObject\NormalToFluent('SomeClass', ['someFunction', 'otherFunction'])]])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        // process only existing statements
        if ($node->stmts === null) {
            return null;
        }
        $classMethodStatementCount = \count((array) $node->stmts);
        // iterate from bottom to up, so we can merge
        for ($i = $classMethodStatementCount - 1; $i >= 0; --$i) {
            if (!isset($node->stmts[$i])) {
                continue;
            }
            /** @var Expression $stmt */
            $stmt = $node->stmts[$i];
            if ($this->shouldSkipPreviousStmt($node, $i)) {
                continue;
            }
            /** @var Expression $prevStmt */
            $prevStmt = $node->stmts[$i - 1];
            // here are 2 method calls statements in a row, while current one is first one
            if (!$this->isBothMethodCallMatch($stmt, $prevStmt)) {
                if (\count($this->collectedMethodCalls) >= 2) {
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
    /**
     * @param array<string, NormalToFluent[]> $configuration
     */
    public function configure(array $configuration) : void
    {
        $callsToFluent = $configuration[self::CALLS_TO_FLUENT] ?? [];
        \RectorPrefix20211020\Webmozart\Assert\Assert::allIsInstanceOf($callsToFluent, \Rector\Defluent\ValueObject\NormalToFluent::class);
        $this->callsToFluent = $callsToFluent;
    }
    private function shouldSkipPreviousStmt(\PhpParser\Node\Stmt\ClassMethod $classMethod, int $i) : bool
    {
        // we look only for 2+ stmts
        if (!isset($classMethod->stmts[$i - 1])) {
            return \true;
        }
        $prevStmt = $classMethod->stmts[$i - 1];
        return !$prevStmt instanceof \PhpParser\Node\Stmt\Expression;
    }
    private function isBothMethodCallMatch(\PhpParser\Node\Stmt\Expression $firstExpression, \PhpParser\Node\Stmt\Expression $secondExpression) : bool
    {
        if (!$firstExpression->expr instanceof \PhpParser\Node\Expr\MethodCall) {
            return \false;
        }
        if (!$secondExpression->expr instanceof \PhpParser\Node\Expr\MethodCall) {
            return \false;
        }
        $firstMethodCallMatchObjectType = $this->matchMethodCall($firstExpression->expr);
        if (!$firstMethodCallMatchObjectType instanceof \PHPStan\Type\ObjectType) {
            return \false;
        }
        $secondMethodCallMatchObjectType = $this->matchMethodCall($secondExpression->expr);
        if (!$secondMethodCallMatchObjectType instanceof \PHPStan\Type\ObjectType) {
            return \false;
        }
        // is the same type
        return $firstMethodCallMatchObjectType->equals($secondMethodCallMatchObjectType);
    }
    private function fluentizeCollectedMethodCalls(\PhpParser\Node\Stmt\ClassMethod $classMethod) : void
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
        $stmt = $classMethod->stmts[$fluentMethodCallIndex];
        if (!$stmt instanceof \PhpParser\Node\Stmt\Expression) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        /** @var MethodCall $fluentMethodCall */
        $fluentMethodCall = $stmt->expr;
        // they are added in reversed direction
        $methodCallsToAdd = \array_reverse($methodCallsToAdd);
        foreach ($methodCallsToAdd as $methodCallToAdd) {
            // make var a parent method call
            $fluentMethodCall->var = new \PhpParser\Node\Expr\MethodCall($fluentMethodCall->var, $methodCallToAdd->name, $methodCallToAdd->args);
        }
    }
    private function matchMethodCall(\PhpParser\Node\Expr\MethodCall $methodCall) : ?\PHPStan\Type\ObjectType
    {
        foreach ($this->callsToFluent as $callToFluent) {
            if (!$this->isObjectType($methodCall->var, $callToFluent->getObjectType())) {
                continue;
            }
            if ($this->isNames($methodCall->name, $callToFluent->getMethodNames())) {
                return $callToFluent->getObjectType();
            }
        }
        return null;
    }
}
