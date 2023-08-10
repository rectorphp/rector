<?php

declare (strict_types=1);
namespace Rector\DeadCode\PhpDoc;

use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\UnionType;
use Rector\NodeTypeResolver\TypeComparator\TypeComparator;
use Rector\StaticTypeMapper\StaticTypeMapper;
final class DeadVarTagValueNodeAnalyzer
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\TypeComparator\TypeComparator
     */
    private $typeComparator;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    public function __construct(TypeComparator $typeComparator, StaticTypeMapper $staticTypeMapper)
    {
        $this->typeComparator = $typeComparator;
        $this->staticTypeMapper = $staticTypeMapper;
    }
    public function isDead(VarTagValueNode $varTagValueNode, Property $property) : bool
    {
        if ($property->type === null) {
            return \false;
        }
        // is strict type superior to doc type? keep strict type only
        $propertyType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($property->type);
        $docType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($varTagValueNode->type, $property);
        if ($propertyType instanceof UnionType && !$docType instanceof UnionType) {
            return !$docType instanceof IntersectionType;
        }
        if (!$this->typeComparator->arePhpParserAndPhpStanPhpDocTypesEqual($property->type, $varTagValueNode->type, $property)) {
            return \false;
        }
        return $varTagValueNode->description === '';
    }
}
