<?php

declare(strict_types=1);

namespace Rector\Core\NodeManipulator;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\NullsafePropertyFetch;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use Rector\Core\Util\StaticInstanceOf;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class NullsafeManipulator
{
    public function processNullSafeExpr(Expr $expr): ?Expr
    {
        if ($expr instanceof MethodCall) {
            return new NullsafeMethodCall($expr->var, $expr->name);
        }

        if ($expr instanceof PropertyFetch) {
            return new NullsafePropertyFetch($expr->var, $expr->name);
        }

        return null;
    }

    public function processNullSafeExprResult(?Expr $expr, Identifier $nextExprIdentifier): ?Expr
    {
        if ($expr === null) {
            return null;
        }

        $parentIdentifier = $nextExprIdentifier->getAttribute(AttributeKey::PARENT_NODE);

        if (StaticInstanceOf::isOneOf($parentIdentifier, [MethodCall::class, NullsafeMethodCall::class])) {
            return new NullsafeMethodCall($expr, $nextExprIdentifier);
        }

        return new NullsafePropertyFetch($expr, $nextExprIdentifier);
    }
}
