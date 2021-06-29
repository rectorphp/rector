<?php

declare (strict_types=1);
namespace Rector\Defluent\NodeResolver;

use PhpParser\Node\Expr;
use Rector\Defluent\ValueObject\AssignAndRootExpr;
use Rector\Defluent\ValueObject\FirstAssignFluentCall;
final class FirstMethodCallVarResolver
{
    /**
     * @param \Rector\Defluent\ValueObject\FirstAssignFluentCall|\Rector\Defluent\ValueObject\AssignAndRootExpr $firstCallFactoryAware
     */
    public function resolve($firstCallFactoryAware, int $key) : \PhpParser\Node\Expr
    {
        if (!$firstCallFactoryAware->isFirstCallFactory()) {
            return $firstCallFactoryAware->getCallerExpr();
        }
        // very first call
        if ($key !== 0) {
            return $firstCallFactoryAware->getCallerExpr();
        }
        return $firstCallFactoryAware->getFactoryAssignVariable();
    }
}
