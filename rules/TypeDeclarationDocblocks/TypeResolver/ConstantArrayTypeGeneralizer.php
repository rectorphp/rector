<?php

declare (strict_types=1);
namespace Rector\TypeDeclarationDocblocks\TypeResolver;

use PHPStan\PhpDocParser\Ast\Type\ArrayShapeNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NeverType;
use PHPStan\Type\Type;
use Rector\Privatization\TypeManipulator\TypeNormalizer;
use Rector\StaticTypeMapper\StaticTypeMapper;
final class ConstantArrayTypeGeneralizer
{
    /**
     * @readonly
     */
    private StaticTypeMapper $staticTypeMapper;
    /**
     * @readonly
     */
    private TypeNormalizer $typeNormalizer;
    /**
     * Using 10-level array @return docblocks makes code very hard to read,
     * lets limit it to reasonable level
     */
    private const MAX_NESTING = 3;
    private int $currentNesting = 0;
    public function __construct(StaticTypeMapper $staticTypeMapper, TypeNormalizer $typeNormalizer)
    {
        $this->staticTypeMapper = $staticTypeMapper;
        $this->typeNormalizer = $typeNormalizer;
    }
    /**
     * @return \PHPStan\PhpDocParser\Ast\Type\GenericTypeNode|\PHPStan\PhpDocParser\Ast\Type\ArrayShapeNode
     */
    public function generalize(ConstantArrayType $constantArrayType, bool $isFresh = \true)
    {
        if ($isFresh) {
            $this->currentNesting = 0;
        } else {
            ++$this->currentNesting;
        }
        $genericKeyType = $this->typeNormalizer->generalizeConstantTypes($constantArrayType->getKeyType());
        $itemType = $constantArrayType->getItemType();
        if ($itemType instanceof NeverType) {
            return ArrayShapeNode::createSealed([]);
        }
        if ($itemType instanceof ConstantArrayType) {
            if ($this->currentNesting >= self::MAX_NESTING) {
                $genericItemType = new MixedType();
            } else {
                $genericItemType = $this->generalize($itemType, \false);
            }
        } else {
            $genericItemType = $this->typeNormalizer->generalizeConstantTypes($itemType);
        }
        // correction
        if ($genericItemType instanceof NeverType) {
            $genericItemType = new MixedType();
        }
        return $this->createArrayGenericTypeNode($genericKeyType, $genericItemType);
    }
    /**
     * @param \PHPStan\Type\Type|\PHPStan\PhpDocParser\Ast\Type\GenericTypeNode|\PHPStan\PhpDocParser\Ast\Type\ArrayShapeNode $itemType
     */
    private function createArrayGenericTypeNode(Type $keyType, $itemType): GenericTypeNode
    {
        $keyDocTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode($keyType);
        if ($itemType instanceof Type) {
            $itemDocTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode($itemType);
        } else {
            $itemDocTypeNode = $itemType;
        }
        return new GenericTypeNode(new IdentifierTypeNode('array'), [$keyDocTypeNode, $itemDocTypeNode]);
    }
}
