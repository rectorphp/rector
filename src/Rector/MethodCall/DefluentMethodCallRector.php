<?php

declare(strict_types=1);

namespace Rector\Core\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PHPStan\Type\TypeWithClassName;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\ValueObject\AssignAndRootExpr;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\Core\Tests\Rector\MethodCall\DefluentMethodCallRector\DefluentMethodCallRectorTest
 */
final class DefluentMethodCallRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $namesToDefluent = [];

    /**
     * @param string[] $namesToDefluent
     */
    public function __construct(array $namesToDefluent = [])
    {
        $this->namesToDefluent = $namesToDefluent;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns fluent interface calls to classic ones.', [new CodeSample(<<<'PHP'
$someClass = new SomeClass();
$someClass->someFunction()
            ->otherFunction();
PHP
            , <<<'PHP'
$someClass = new SomeClass();
$someClass->someFunction();
$someClass->otherFunction();
PHP
        )]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isLastMethodCallInChainCall($node)) {
            return null;
        }

        $chainMethodCalls = $this->collectAllMethodCallsInChain($node);
        $assignAndRootExpr = $this->extractRootVar($chainMethodCalls);
        if ($assignAndRootExpr === null) {
            return null;
        }

        if (! $this->isCalleeSingleType($assignAndRootExpr, $chainMethodCalls)) {
            return null;
        }

        if (! $this->isRootVariableMatch($assignAndRootExpr->getRootExpr())) {
            return null;
        }

        $decoupledMethodCalls = $this->createNonFluentMethodCalls($chainMethodCalls, $assignAndRootExpr);
        $currentOne = array_pop($decoupledMethodCalls);

        // add separated method calls
        /** @var MethodCall[] $decoupledMethodCalls */
        $decoupledMethodCalls = array_reverse($decoupledMethodCalls);
        foreach ($decoupledMethodCalls as $decoupledMethodCall) {
            // needed to remove weird spacing
            $decoupledMethodCall->setAttribute('origNode', null);
            $this->addNodeAfterNode($decoupledMethodCall, $node);
        }

        return $currentOne;
    }

    private function isLastMethodCallInChainCall(MethodCall $methodCall): bool
    {
        // is chain method call
        if (! $methodCall->var instanceof MethodCall && ! $methodCall->var instanceof New_) {
            return false;
        }
        $nextNode = $methodCall->getAttribute(AttributeKey::NEXT_NODE);
        // is last chain call
        return $nextNode === null;
    }

    /**
     * @return MethodCall[]
     */
    private function collectAllMethodCallsInChain(MethodCall $methodCall): array
    {
        $chainMethodCalls = [$methodCall];
        $currentNode = $methodCall->var;
        while ($currentNode instanceof MethodCall) {
            $chainMethodCalls[] = $currentNode;
            $currentNode = $currentNode->var;
        }

        return $chainMethodCalls;
    }

    /**
     * @param MethodCall[] $chainMethodCalls
     * @return Expr[]
     */
    private function createNonFluentMethodCalls(array $chainMethodCalls, AssignAndRootExpr $assignAndRootExpr): array
    {
        $decoupledMethodCalls = [];

        foreach ($chainMethodCalls as $chainMethodCall) {
            $chainMethodCall->var = $assignAndRootExpr->getAssignExpr();
            $decoupledMethodCalls[] = $chainMethodCall;
        }

        if ($assignAndRootExpr->getRootExpr() instanceof New_) {
            $decoupledMethodCalls[] = $assignAndRootExpr->getRootExpr();
        }

        return $decoupledMethodCalls;
    }

    /**
     * @param MethodCall[] $methodCalls
     */
    private function extractRootVar(array $methodCalls): ?AssignAndRootExpr
    {
        foreach ($methodCalls as $methodCall) {
            if ($methodCall->var instanceof Variable) {
                return new AssignAndRootExpr($methodCall->var, $methodCall->var);
            }

            if ($methodCall->var instanceof PropertyFetch) {
                return new AssignAndRootExpr($methodCall->var, $methodCall->var);
            }

            if ($methodCall->var instanceof New_) {
                // we need assigned left variable here
                $previousAssign = $this->betterNodeFinder->findFirstPreviousOfTypes($methodCall->var, [Assign::class]);
                if ($previousAssign instanceof Assign) {
                    return new AssignAndRootExpr($previousAssign->var, $methodCall->var);
                }

                // no assign, just standalone call
                return null;
            }
        }

        return null;
    }

    /**
     * @todo match type - extract to matching a service
     */
    private function isRootVariableMatch(Expr $expr): bool
    {
        $exprType = $this->getStaticType($expr);

        if ($exprType instanceof TypeWithClassName) {
            $className = $exprType->getClassName();
            foreach ($this->namesToDefluent as $nameToDefluent) {
                if (is_a($className, $nameToDefluent, true)) {
                    return true;
                }

                if (fnmatch($nameToDefluent, $className)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param MethodCall[] $chainMethodCalls
     */
    private function isCalleeSingleType(AssignAndRootExpr $assignAndRootExpr, array $chainMethodCalls): bool
    {
        $rootStaticType = $this->getStaticType($assignAndRootExpr->getRootExpr());

        foreach ($chainMethodCalls as $chainMethodCall) {
            $chainMethodCallStaticType = $this->getStaticType($chainMethodCall);
            if (! $chainMethodCallStaticType->equals($rootStaticType)) {
                return false;
            }
        }

        return true;
    }
}
