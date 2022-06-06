<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\TypeDeclaration;

use RectorPrefix20220606\PhpParser\Node\ComplexType;
use RectorPrefix20220606\PhpParser\Node\Identifier;
use RectorPrefix20220606\PhpParser\Node\IntersectionType;
use RectorPrefix20220606\PhpParser\Node\Name;
use RectorPrefix20220606\PhpParser\Node\NullableType;
use RectorPrefix20220606\PhpParser\Node\UnionType;
use RectorPrefix20220606\Rector\StaticTypeMapper\StaticTypeMapper;
final class PhpParserTypeAnalyzer
{
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    public function __construct(StaticTypeMapper $staticTypeMapper)
    {
        $this->staticTypeMapper = $staticTypeMapper;
    }
    /**
     * @param \PhpParser\Node\Name|\PhpParser\Node\NullableType|\PhpParser\Node\UnionType|\PhpParser\Node\Identifier|\PhpParser\Node\IntersectionType $possibleSubtype
     * @param \PhpParser\Node\Name|\PhpParser\Node\NullableType|\PhpParser\Node\UnionType|\PhpParser\Node\Identifier|\PhpParser\Node\ComplexType $possibleParentType
     */
    public function isCovariantSubtypeOf($possibleSubtype, $possibleParentType) : bool
    {
        // skip until PHP 8 is out
        if ($this->isUnionType($possibleSubtype, $possibleParentType)) {
            return \false;
        }
        // possible - https://3v4l.org/ZuJCh
        if ($possibleSubtype instanceof NullableType && !$possibleParentType instanceof NullableType) {
            return $this->isCovariantSubtypeOf($possibleSubtype->type, $possibleParentType);
        }
        // not possible - https://3v4l.org/iNDTc
        if (!$possibleSubtype instanceof NullableType && $possibleParentType instanceof NullableType) {
            return \false;
        }
        $subtypeType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($possibleParentType);
        $parentType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($possibleSubtype);
        return $parentType->isSuperTypeOf($subtypeType)->yes();
    }
    /**
     * @param \PhpParser\Node\Identifier|\PhpParser\Node\Name|\PhpParser\Node\NullableType|\PhpParser\Node\UnionType|\PhpParser\Node\IntersectionType $possibleSubtype
     * @param \PhpParser\Node\ComplexType|\PhpParser\Node\Identifier|\PhpParser\Node\Name $possibleParentType
     */
    private function isUnionType($possibleSubtype, $possibleParentType) : bool
    {
        if ($possibleSubtype instanceof UnionType) {
            return \true;
        }
        return $possibleParentType instanceof UnionType;
    }
}
