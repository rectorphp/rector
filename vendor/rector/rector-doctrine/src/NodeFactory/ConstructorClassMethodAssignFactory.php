<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Doctrine\NodeFactory;

use RectorPrefix20220606\PhpParser\Node\Expr\Assign;
use RectorPrefix20220606\PhpParser\Node\Expr\PropertyFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\PhpParser\Node\Stmt\Expression;
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
