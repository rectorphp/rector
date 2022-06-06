<?php

declare (strict_types=1);
namespace Rector\DeadCode\PhpDoc;

use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
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
    public function __construct(\Rector\NodeTypeResolver\TypeComparator\TypeComparator $typeComparator, \Rector\StaticTypeMapper\StaticTypeMapper $staticTypeMapper)
    {
        $this->typeComparator = $typeComparator;
        $this->staticTypeMapper = $staticTypeMapper;
    }
    public function isDead(\PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode $varTagValueNode, \PhpParser\Node\Stmt\Property $property) : bool
    {
        if ($property->type === null) {
            return \false;
        }
        // is strict type superior to doc type? keep strict type only
        $propertyType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($property->type);
        $docType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($varTagValueNode->type, $property);
        if ($propertyType instanceof \PHPStan\Type\UnionType && !$docType instanceof \PHPStan\Type\UnionType) {
            return \true;
        }
        if (!$this->typeComparator->arePhpParserAndPhpStanPhpDocTypesEqual($property->type, $varTagValueNode->type, $property)) {
            return \false;
        }
        return $varTagValueNode->description === '';
    }
}
