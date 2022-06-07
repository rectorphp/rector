<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeFactory\FormType;

use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
final class BuildFormOptionAssignsFactory
{
    /**
     * @param string[] $paramNames
     * @return Expression[]
     */
    public function createDimFetchAssignsFromParamNames(array $paramNames) : array
    {
        $expressions = [];
        foreach ($paramNames as $paramName) {
            $arrayDimFetch = new ArrayDimFetch(new Variable('options'), new String_($paramName));
            $assign = new Assign(new Variable($paramName), $arrayDimFetch);
            $expressions[] = new Expression($assign);
        }
        return $expressions;
    }
}
