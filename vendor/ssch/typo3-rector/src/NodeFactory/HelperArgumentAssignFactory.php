<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\NodeFactory;

use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\NodeNameResolver\NodeNameResolver;
final class HelperArgumentAssignFactory
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @return Expression[]
     */
    public function createRegisterArgumentsCalls(\PhpParser\Node\Stmt\ClassMethod $renderMethod) : array
    {
        $stmts = [];
        foreach ($renderMethod->params as $param) {
            /** @var string $paramName */
            $paramName = $this->nodeNameResolver->getName($param->var);
            $propertyFetch = new \PhpParser\Node\Expr\PropertyFetch(new \PhpParser\Node\Expr\Variable('this'), 'arguments');
            $argumentsDimFetch = new \PhpParser\Node\Expr\ArrayDimFetch($propertyFetch, new \PhpParser\Node\Scalar\String_($paramName));
            $assign = new \PhpParser\Node\Expr\Assign(new \PhpParser\Node\Expr\Variable($paramName), $argumentsDimFetch);
            $stmts[] = new \PhpParser\Node\Stmt\Expression($assign);
        }
        // remove all params
        $renderMethod->params = [];
        return $stmts;
    }
}
