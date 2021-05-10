<?php

declare (strict_types=1);
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
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(\Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    public function isStringTypeExpr(\PhpParser\Node\Expr $expr) : bool
    {
        $staticType = $this->nodeTypeResolver->getStaticType($expr);
        return $this->isStringType($staticType);
    }
    private function isStringType(\PHPStan\Type\Type $type) : bool
    {
        if ($type instanceof \PHPStan\Type\StringType) {
            return \true;
        }
        if ($type instanceof \PHPStan\Type\Accessory\AccessoryNumericStringType) {
            return \true;
        }
        if ($type instanceof \PHPStan\Type\IntersectionType || $type instanceof \PHPStan\Type\UnionType) {
            foreach ($type->getTypes() as $innerType) {
                if (!$this->isStringType($innerType)) {
                    return \false;
                }
            }
            return \true;
        }
        return \false;
    }
}
