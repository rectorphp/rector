<?php

declare (strict_types=1);
namespace Rector\Core\NodeManipulator;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\NullsafePropertyFetch;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class NullsafeManipulator
{
    /**
     * @return \PhpParser\Node\Expr\NullsafeMethodCall|\PhpParser\Node\Expr\NullsafePropertyFetch|null
     */
    public function processNullSafeExpr(\PhpParser\Node\Expr $expr)
    {
        if ($expr instanceof \PhpParser\Node\Expr\MethodCall) {
            return new \PhpParser\Node\Expr\NullsafeMethodCall($expr->var, $expr->name, $expr->args);
        }
        if ($expr instanceof \PhpParser\Node\Expr\PropertyFetch) {
            return new \PhpParser\Node\Expr\NullsafePropertyFetch($expr->var, $expr->name);
        }
        return null;
    }
    public function processNullSafeExprResult(?\PhpParser\Node\Expr $expr, \PhpParser\Node\Identifier $nextExprIdentifier) : ?\PhpParser\Node\Expr
    {
        if ($expr === null) {
            return null;
        }
        $parentIdentifier = $nextExprIdentifier->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if ($parentIdentifier instanceof \PhpParser\Node\Expr\MethodCall || $parentIdentifier instanceof \PhpParser\Node\Expr\NullsafeMethodCall) {
            return new \PhpParser\Node\Expr\NullsafeMethodCall($expr, $nextExprIdentifier, $parentIdentifier->args);
        }
        return new \PhpParser\Node\Expr\NullsafePropertyFetch($expr, $nextExprIdentifier);
    }
}
