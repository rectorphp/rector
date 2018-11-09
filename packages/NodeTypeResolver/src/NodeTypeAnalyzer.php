<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver;

use Countable;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;
use PHPStan\Type\Accessory\HasOffsetType;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\UnionType;
use Rector\NodeTypeResolver\Node\Attribute;

final class NodeTypeAnalyzer
{
    public function isStringType(Node $node): bool
    {
        /** @var Scope|null $nodeScope */
        $nodeScope = $node->getAttribute(Attribute::SCOPE);
        if (! $node instanceof Expr || $nodeScope === null) {
            return false;
        }

        $nodeType = $nodeScope->getType($node);

        return $nodeType instanceof StringType;
    }

    /**
     * e.g. string|null, ObjectNull|null
     */
    public function isNullableType(Node $node): bool
    {
        /** @var Scope|null $nodeScope */
        $nodeScope = $node->getAttribute(Attribute::SCOPE);
        if (! $node instanceof Expr || $nodeScope === null) {
            return false;
        }

        /** @var Expr $node */
        $nodeType = $nodeScope->getType($node);

        if (! $nodeType instanceof UnionType) {
            return false;
        }

        return $nodeType->isSuperTypeOf(new NullType())->yes();
    }

    public function isBoolType(Node $node): bool
    {
        /** @var Scope|null $nodeScope */
        $nodeScope = $node->getAttribute(Attribute::SCOPE);
        if (! $node instanceof Expr || $nodeScope === null) {
            return false;
        }

        /** @var Expr $node */
        $nodeType = $nodeScope->getType($node);

        return $nodeType instanceof BooleanType;
    }

    public function isCountableType(Node $node): bool
    {
        /** @var Scope|null $nodeScope */
        $nodeScope = $node->getAttribute(Attribute::SCOPE);
        if (! $node instanceof Expr || $nodeScope === null) {
            return false;
        }

        $nodeType = $nodeScope->getType($node);

        if ($nodeType instanceof ObjectType) {
            return is_a($nodeType->getClassName(), Countable::class, true);
        }

        if ($nodeType instanceof IntersectionType) {
            foreach ($nodeType->getTypes() as $intersectionNodeType) {
                if ($intersectionNodeType instanceof ArrayType || $intersectionNodeType instanceof HasOffsetType) {
                    continue;
                }

                return false;
            }

            return true;
        }

        return $nodeType instanceof ArrayType;
    }
}
