<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\TypeAnalyzer;

use PhpParser\Node\Expr;
use PHPStan\Type\StringType;
use PHPStan\Type\UnionType;
use Rector\NodeTypeResolver\NodeTypeResolver;
final class StringTypeAnalyzer
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    public function isStringOrUnionStringOnlyType(Expr $expr) : bool
    {
        $nodeType = $this->nodeTypeResolver->getType($expr);
        if ($nodeType instanceof StringType) {
            return \true;
        }
        if ($nodeType instanceof UnionType) {
            foreach ($nodeType->getTypes() as $singleType) {
                if ($singleType->isSuperTypeOf(new StringType())->no()) {
                    return \false;
                }
            }
            return \true;
        }
        return \false;
    }
}
