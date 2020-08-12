<?php

declare(strict_types=1);

namespace Rector\Doctrine\NodeFactory;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Expression;

final class RepositoryNodeFactory
{
    public function createRepositoryAssign(Expr $entityReferenceExpr): Expression
    {
        $propertyFetch = new PropertyFetch(new Variable('this'), new Identifier('repository'));
        $assign = new Assign($propertyFetch, $this->createGetRepositoryMethodCall($entityReferenceExpr));

        return new Expression($assign);
    }

    private function createGetRepositoryMethodCall(Expr $entityReferenceExpr): MethodCall
    {
        $args = [new Arg($entityReferenceExpr)];

        return new MethodCall(new Variable('entityManager'), 'getRepository', $args);
    }
}
