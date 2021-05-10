<?php

declare (strict_types=1);
namespace Rector\Doctrine\NodeFactory;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
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
