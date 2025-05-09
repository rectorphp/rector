<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony73\NodeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ObjectType;
use Rector\NodeTypeResolver\NodeTypeResolver;
final class LocalArrayMethodCallableMatcher
{
    /**
     * @readonly
     */
    private NodeTypeResolver $nodeTypeResolver;
    public function __construct(NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    public function match(Expr $expr, ObjectType $objectType) : ?string
    {
        if ($expr instanceof MethodCall) {
            if (!$expr->name instanceof Identifier) {
                return null;
            }
            if (!$this->nodeTypeResolver->isObjectType($expr->var, $objectType)) {
                return null;
            }
            return $expr->name->toString();
        }
        if ($expr instanceof Array_) {
            if (!$this->nodeTypeResolver->isObjectType($expr->items[0]->value, $objectType)) {
                return null;
            }
            $secondItem = $expr->items[1];
            if (!$secondItem->value instanceof String_) {
                return null;
            }
            return $secondItem->value->value;
        }
        return null;
    }
}
