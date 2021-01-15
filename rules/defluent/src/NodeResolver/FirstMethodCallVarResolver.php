<?php

declare(strict_types=1);

namespace Rector\Defluent\NodeResolver;

use PhpParser\Node\Expr;
use Rector\Defluent\Contract\ValueObject\FirstCallFactoryAwareInterface;
use Rector\Defluent\ValueObject\AssignAndRootExpr;
use Rector\Defluent\ValueObject\FirstAssignFluentCall;

final class FirstMethodCallVarResolver
{
    /**
     * @param FirstAssignFluentCall|AssignAndRootExpr $firstCallFactoryAware
     */
    public function resolve(FirstCallFactoryAwareInterface $firstCallFactoryAware, int $key): Expr
    {
        if (! $firstCallFactoryAware->isFirstCallFactory()) {
            return $firstCallFactoryAware->getCallerExpr();
        }

        // very first call
        if ($key !== 0) {
            return $firstCallFactoryAware->getCallerExpr();
        }

        return $firstCallFactoryAware->getFactoryAssignVariable();
    }
}
