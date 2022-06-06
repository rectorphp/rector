<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Doctrine\NodeFactory;

use RectorPrefix20220606\PhpParser\Node\Arg;
use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Expr\Assign;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PhpParser\Node\Expr\PropertyFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\PhpParser\Node\Identifier;
final class RepositoryNodeFactory
{
    public function createRepositoryAssign(Expr $entityReferenceExpr) : Assign
    {
        $propertyFetch = new PropertyFetch(new Variable('this'), new Identifier('repository'));
        return new Assign($propertyFetch, $this->createGetRepositoryMethodCall($entityReferenceExpr));
    }
    private function createGetRepositoryMethodCall(Expr $entityReferenceExpr) : MethodCall
    {
        $args = [new Arg($entityReferenceExpr)];
        return new MethodCall(new Variable('entityManager'), 'getRepository', $args);
    }
}
