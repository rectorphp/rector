<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\NodeTypeResolver\TypeAnalyzer;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PHPStan\Type\StringType;
use RectorPrefix20220606\PHPStan\Type\UnionType;
use RectorPrefix20220606\Rector\NodeTypeResolver\NodeTypeResolver;
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
    public function isStringOrUnionStringOnlyType(Node $node) : bool
    {
        $nodeType = $this->nodeTypeResolver->getType($node);
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
