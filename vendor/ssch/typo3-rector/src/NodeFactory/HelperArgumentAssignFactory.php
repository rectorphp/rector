<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\NodeFactory;

use RectorPrefix20220606\PhpParser\Node\Expr\ArrayDimFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\Assign;
use RectorPrefix20220606\PhpParser\Node\Expr\PropertyFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\PhpParser\Node\Scalar\String_;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\PhpParser\Node\Stmt\Expression;
use RectorPrefix20220606\Rector\NodeNameResolver\NodeNameResolver;
final class HelperArgumentAssignFactory
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @return Expression[]
     */
    public function createRegisterArgumentsCalls(ClassMethod $renderMethod) : array
    {
        $stmts = [];
        foreach ($renderMethod->params as $param) {
            /** @var string $paramName */
            $paramName = $this->nodeNameResolver->getName($param->var);
            $propertyFetch = new PropertyFetch(new Variable('this'), 'arguments');
            $argumentsDimFetch = new ArrayDimFetch($propertyFetch, new String_($paramName));
            $assign = new Assign(new Variable($paramName), $argumentsDimFetch);
            $stmts[] = new Expression($assign);
        }
        // remove all params
        $renderMethod->params = [];
        return $stmts;
    }
}
