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
            $arrayDimFetch = new \PhpParser\Node\Expr\ArrayDimFetch(new \PhpParser\Node\Expr\Variable('options'), new \PhpParser\Node\Scalar\String_($paramName));
            $assign = new \PhpParser\Node\Expr\Assign(new \PhpParser\Node\Expr\Variable($paramName), $arrayDimFetch);
            $expressions[] = new \PhpParser\Node\Stmt\Expression($assign);
        }
        return $expressions;
    }
}
