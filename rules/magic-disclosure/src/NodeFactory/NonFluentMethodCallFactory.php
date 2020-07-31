<?php

declare(strict_types=1);

namespace Rector\MagicDisclosure\NodeFactory;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use Rector\MagicDisclosure\ValueObject\AssignAndRootExpr;

final class NonFluentMethodCallFactory
{
    /**
     * @param MethodCall[] $chainMethodCalls
     */
    public function createFromAssignObjectAndMethodCalls(
        AssignAndRootExpr $assignAndRootExpr,
        array $chainMethodCalls
    ): array {
        $nodesToAdd = [];

        if ($this->isNewNodeNeeded($assignAndRootExpr)) {
            $nodesToAdd[] = $assignAndRootExpr->getFirstAssign();
        }

        $decoupledMethodCalls = $this->createNonFluentMethodCalls($chainMethodCalls, $assignAndRootExpr);
        $nodesToAdd = array_merge($nodesToAdd, $decoupledMethodCalls);

        if ($assignAndRootExpr->getSilentVariable() !== null) {
            $nodesToAdd[] = $assignAndRootExpr->getReturnSilentVariable();
        }

        return $nodesToAdd;
    }

    /**
     * @param MethodCall[] $chainMethodCalls
     * @return Expr[]
     */
    private function createNonFluentMethodCalls(array $chainMethodCalls, AssignAndRootExpr $assignAndRootExpr): array
    {
        $decoupledMethodCalls = [];

        foreach ($chainMethodCalls as $chainMethodCall) {
            $chainMethodCall->var = $assignAndRootExpr->getCallerExpr();
            $decoupledMethodCalls[] = $chainMethodCall;
        }

        if ($assignAndRootExpr->getRootExpr() instanceof New_) {
            if ($assignAndRootExpr->getSilentVariable() !== null) {
                $decoupledMethodCalls[] = new Assign(
                    $assignAndRootExpr->getSilentVariable(),
                    $assignAndRootExpr->getRootExpr()
                );
            } elseif (! $assignAndRootExpr->getRootExpr() instanceof New_) {
                $decoupledMethodCalls[] = $assignAndRootExpr->getRootExpr();
            }
        }

        return array_reverse($decoupledMethodCalls);
    }

    private function isNewNodeNeeded(AssignAndRootExpr $assignAndRootExpr): bool
    {
        if (! $assignAndRootExpr->getRootExpr() instanceof New_) {
            return false;
        }

        return $assignAndRootExpr->getRootExpr() !== $assignAndRootExpr->getAssignExpr();
    }
}
