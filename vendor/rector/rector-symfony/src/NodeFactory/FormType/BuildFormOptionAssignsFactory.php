<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Symfony\NodeFactory\FormType;

use RectorPrefix20220606\PhpParser\Node\Expr\ArrayDimFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\Assign;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\PhpParser\Node\Scalar\String_;
use RectorPrefix20220606\PhpParser\Node\Stmt\Expression;
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
