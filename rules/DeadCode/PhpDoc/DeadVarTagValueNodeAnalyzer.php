<?php

declare(strict_types=1);

namespace Rector\DeadCode\PhpDoc;

use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\Type\UnionType;
use Rector\NodeTypeResolver\TypeComparator\TypeComparator;
use Rector\StaticTypeMapper\StaticTypeMapper;

final class DeadVarTagValueNodeAnalyzer
{
    public function __construct(
        private readonly TypeComparator $typeComparator,
        private readonly StaticTypeMapper $staticTypeMapper,
    ) {
    }

    public function isDead(VarTagValueNode $varTagValueNode, Property $property): bool
    {
        if ($property->type === null) {
            return false;
        }

        // is strict type superior to doc type? keep strict type only
        $propertyType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($property->type);
        $docType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($varTagValueNode->type, $property);

        if ($propertyType instanceof UnionType && ! $docType instanceof UnionType) {
            return true;
        }

        if (! $this->typeComparator->arePhpParserAndPhpStanPhpDocTypesEqual(
            $property->type,
            $varTagValueNode->type,
            $property
        )) {
            return false;
        }

        return $varTagValueNode->description === '';
    }
}
