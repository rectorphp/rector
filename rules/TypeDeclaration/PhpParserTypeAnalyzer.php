<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\UnionType;
use Rector\StaticTypeMapper\StaticTypeMapper;

final class PhpParserTypeAnalyzer
{
    public function __construct(
        private StaticTypeMapper $staticTypeMapper
    ) {
    }

    /**
     * @param Name|NullableType|UnionType|Identifier $possibleSubtype
     * @param Name|NullableType|UnionType|Identifier $possibleParentType
     */
    public function isCovariantSubtypeOf(Node $possibleSubtype, Node $possibleParentType): bool
    {
        // skip until PHP 8 is out
        if ($this->isUnionType($possibleSubtype, $possibleParentType)) {
            return false;
        }

        // possible - https://3v4l.org/ZuJCh
        if ($possibleSubtype instanceof NullableType && ! $possibleParentType instanceof NullableType) {
            return $this->isCovariantSubtypeOf($possibleSubtype->type, $possibleParentType);
        }

        // not possible - https://3v4l.org/iNDTc
        if (! $possibleSubtype instanceof NullableType && $possibleParentType instanceof NullableType) {
            return false;
        }

        $subtypeType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($possibleParentType);
        $parentType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($possibleSubtype);

        return $parentType->isSuperTypeOf($subtypeType)
            ->yes();
    }

    private function isUnionType(Node $possibleSubtype, Node $possibleParentType): bool
    {
        if ($possibleSubtype instanceof UnionType) {
            return true;
        }

        return $possibleParentType instanceof UnionType;
    }
}
