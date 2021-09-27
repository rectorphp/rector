<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration;

use PhpParser\Node;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\UnionType;
use Rector\StaticTypeMapper\StaticTypeMapper;
final class PhpParserTypeAnalyzer
{
    /**
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    public function __construct(\Rector\StaticTypeMapper\StaticTypeMapper $staticTypeMapper)
    {
        $this->staticTypeMapper = $staticTypeMapper;
    }
    /**
     * @param \PhpParser\Node\Name|\PhpParser\Node\NullableType|\PhpParser\Node\UnionType|\PhpParser\Node\Identifier $possibleSubtype
     * @param \PhpParser\Node\Name|\PhpParser\Node\NullableType|\PhpParser\Node\UnionType|\PhpParser\Node\Identifier|\PhpParser\Node\ComplexType $possibleParentType
     */
    public function isCovariantSubtypeOf($possibleSubtype, $possibleParentType) : bool
    {
        // skip until PHP 8 is out
        if ($this->isUnionType($possibleSubtype, $possibleParentType)) {
            return \false;
        }
        // possible - https://3v4l.org/ZuJCh
        if ($possibleSubtype instanceof \PhpParser\Node\NullableType && !$possibleParentType instanceof \PhpParser\Node\NullableType) {
            return $this->isCovariantSubtypeOf($possibleSubtype->type, $possibleParentType);
        }
        // not possible - https://3v4l.org/iNDTc
        if (!$possibleSubtype instanceof \PhpParser\Node\NullableType && $possibleParentType instanceof \PhpParser\Node\NullableType) {
            return \false;
        }
        $subtypeType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($possibleParentType);
        $parentType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($possibleSubtype);
        return $parentType->isSuperTypeOf($subtypeType)->yes();
    }
    private function isUnionType(\PhpParser\Node $possibleSubtype, \PhpParser\Node $possibleParentType) : bool
    {
        if ($possibleSubtype instanceof \PhpParser\Node\UnionType) {
            return \true;
        }
        return $possibleParentType instanceof \PhpParser\Node\UnionType;
    }
}
