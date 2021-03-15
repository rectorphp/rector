<?php

declare(strict_types=1);

namespace Rector\Php73\NodeTypeAnalyzer;

use PhpParser\Node\Expr;
use PHPStan\Type\Accessory\AccessoryNumericStringType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\NodeTypeResolver\NodeTypeResolver;

final class NodeTypeAnalyzer
{
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    public function isStringTypeExpr(Expr $expr): bool
    {
        $staticType = $this->nodeTypeResolver->getStaticType($expr);
        return $this->isStringType($staticType);
    }

    private function isStringType(Type $type): bool
    {
        if ($type instanceof StringType) {
            return true;
        }

        if ($type instanceof AccessoryNumericStringType) {
            return true;
        }

        if ($type instanceof IntersectionType || $type instanceof UnionType) {
            foreach ($type->getTypes() as $innerType) {
                if (! $this->isStringType($innerType)) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }
}
