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
            $propertyFetch = new \PhpParser\Node\Expr\PropertyFetch(new \PhpParser\Node\Expr\Variable('this'), $paramName);
            $assign = new \PhpParser\Node\Expr\Assign($propertyFetch, new \PhpParser\Node\Expr\Variable($paramName));
            $expressions[] = new \PhpParser\Node\Stmt\Expression($assign);
        }
        return $expressions;
    }
}
