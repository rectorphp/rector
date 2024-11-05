<?php

declare (strict_types=1);
namespace Rector\DeadCode\PhpDoc;

use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\UnionType;
use Rector\DeadCode\PhpDoc\Guard\TemplateTypeRemovalGuard;
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
    /**
     * @readonly
     * @var \Rector\DeadCode\PhpDoc\Guard\TemplateTypeRemovalGuard
     */
    private $templateTypeRemovalGuard;
    public function __construct(TypeComparator $typeComparator, StaticTypeMapper $staticTypeMapper, TemplateTypeRemovalGuard $templateTypeRemovalGuard)
    {
        $this->typeComparator = $typeComparator;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->templateTypeRemovalGuard = $templateTypeRemovalGuard;
    }
    public function isDead(VarTagValueNode $varTagValueNode, Property $property) : bool
    {
        if ($property->type === null) {
            return \false;
        }
        if ($varTagValueNode->description !== '') {
            return \false;
        }
        // is strict type superior to doc type? keep strict type only
        $propertyType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($property->type);
        $docType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($varTagValueNode->type, $property);
        if (!$this->templateTypeRemovalGuard->isLegal($docType)) {
            return \false;
        }
        if ($propertyType instanceof UnionType && !$docType instanceof UnionType) {
            return !$docType instanceof IntersectionType;
        }
        if ($propertyType instanceof ObjectType && $docType instanceof ObjectType) {
            // more specific type is already in the property
            return $docType->isSuperTypeOf($propertyType)->yes();
        }
        if ($this->typeComparator->arePhpParserAndPhpStanPhpDocTypesEqual($property->type, $varTagValueNode->type, $property)) {
            return \true;
        }
        return $docType instanceof UnionType && $this->typeComparator->areTypesEqual(TypeCombinator::removeNull($docType), $propertyType);
    }
}
