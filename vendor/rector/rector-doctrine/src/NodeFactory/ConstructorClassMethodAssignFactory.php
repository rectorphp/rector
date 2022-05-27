<?php

declare (strict_types=1);
namespace Rector\Doctrine\NodeFactory;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
final class ConstructorClassMethodAssignFactory
{
    /**
     * @param string[] $paramNames
     * @return Expression[]
     */
    public function createFromParamNames(array $paramNames) : array
    {
        $expressions = [];
        foreach ($paramNames as $paramName) {
            $propertyFetch = new PropertyFetch(new Variable('this'), $paramName);
            $assign = new Assign($propertyFetch, new Variable($paramName));
            $expressions[] = new Expression($assign);
        }
        return $expressions;
    }
}
