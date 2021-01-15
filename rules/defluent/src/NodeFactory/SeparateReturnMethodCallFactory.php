<?php

declare(strict_types=1);

namespace Rector\Defluent\NodeFactory;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\Return_;
use Rector\Defluent\NodeResolver\FirstMethodCallVarResolver;
use Rector\Defluent\ValueObject\FirstAssignFluentCall;
use Rector\Defluent\ValueObject\FluentMethodCalls;

final class SeparateReturnMethodCallFactory
{
    /**
     * @var FirstMethodCallVarResolver
     */
    private $firstMethodCallVarResolver;

    public function __construct(FirstMethodCallVarResolver $firstMethodCallVarResolver)
    {
        $this->firstMethodCallVarResolver = $firstMethodCallVarResolver;
    }

    /**
     * @return Node[]
     */
    public function createReturnFromFirstAssignFluentCallAndFluentMethodCalls(
        FirstAssignFluentCall $firstAssignFluentCall,
        FluentMethodCalls $fluentMethodCalls
    ): array {
        $nodesToAdd = [];

        if (! $firstAssignFluentCall->getAssignExpr() instanceof PropertyFetch) {
            $nodesToAdd[] = $firstAssignFluentCall->createFirstAssign();
        }

        $decoupledMethodCalls = $this->createNonFluentMethodCalls(
            $fluentMethodCalls->getFluentMethodCalls(),
            $firstAssignFluentCall,
            true
        );

        $nodesToAdd = array_merge($nodesToAdd, $decoupledMethodCalls);

        // return the first value
        $nodesToAdd[] = new Return_($firstAssignFluentCall->getAssignExpr());

        return $nodesToAdd;
    }

    /**
     * @param MethodCall[] $chainMethodCalls
     * @return MethodCall[]
     */
    private function createNonFluentMethodCalls(
        array $chainMethodCalls,
        FirstAssignFluentCall $firstAssignFluentCall,
        bool $isNewNodeNeeded
    ): array {
        $decoupledMethodCalls = [];

        $lastKey = array_key_last($chainMethodCalls);

        foreach ($chainMethodCalls as $key => $chainMethodCall) {
            // skip first, already handled
            if ($key === $lastKey && $firstAssignFluentCall->isFirstCallFactory() && $isNewNodeNeeded) {
                continue;
            }

            $chainMethodCall->var = $this->firstMethodCallVarResolver->resolve($firstAssignFluentCall, $key);
            $decoupledMethodCalls[] = $chainMethodCall;
        }

        return array_reverse($decoupledMethodCalls);
    }
}
