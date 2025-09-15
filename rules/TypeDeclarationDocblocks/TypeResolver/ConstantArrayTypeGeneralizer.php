<?php

declare (strict_types=1);
namespace Rector\TypeDeclarationDocblocks\TypeResolver;

use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NeverType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\StaticTypeMapper\StaticTypeMapper;
final class ConstantArrayTypeGeneralizer
{
    /**
     * @readonly
     */
    private TypeFactory $typeFactory;
    /**
     * @readonly
     */
    private StaticTypeMapper $staticTypeMapper;
    /**
     * Using 10-level array @return docblocks makes code very hard to read,
     * lets limit it to reasonable level
     */
    private const MAX_NESTING = 3;
    private int $currentNesting = 0;
    public function __construct(TypeFactory $typeFactory, StaticTypeMapper $staticTypeMapper)
    {
        $this->typeFactory = $typeFactory;
        $this->staticTypeMapper = $staticTypeMapper;
    }
    public function generalize(ConstantArrayType $constantArrayType, bool $isFresh = \true): GenericTypeNode
    {
        if ($isFresh) {
            $this->currentNesting = 0;
        } else {
            ++$this->currentNesting;
        }
        $genericKeyType = $this->constantToGenericType($constantArrayType->getKeyType());
        if ($constantArrayType->getItemType() instanceof NeverType) {
            $genericKeyType = new IntegerType();
        }
        $itemType = $constantArrayType->getItemType();
        if ($itemType instanceof ConstantArrayType) {
            if ($this->currentNesting >= self::MAX_NESTING) {
                $genericItemType = new MixedType();
            } else {
                $genericItemType = $this->generalize($itemType, \false);
            }
        } else {
            $genericItemType = $this->constantToGenericType($itemType);
        }
        return $this->createArrayGenericTypeNode($genericKeyType, $genericItemType);
    }
    /**
     * @param \PHPStan\Type\Type|\PHPStan\PhpDocParser\Ast\Type\GenericTypeNode $itemType
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
    /**
     * Covers constant types too and makes them more generic
     */
    private function constantToGenericType(Type $type): Type
    {
        if ($type->isString()->yes()) {
            return new StringType();
        }
        if ($type->isInteger()->yes()) {
            return new IntegerType();
        }
        if ($type->isBoolean()->yes()) {
            return new BooleanType();
        }
        if ($type->isFloat()->yes()) {
            return new FloatType();
        }
        if ($type->isObject()->yes()) {
            return new ObjectWithoutClassType();
        }
        if ($type instanceof UnionType || $type instanceof IntersectionType) {
            $genericComplexTypes = [];
            foreach ($type->getTypes() as $splitType) {
                $genericComplexTypes[] = $this->constantToGenericType($splitType);
            }
            $genericComplexTypes = $this->typeFactory->uniquateTypes($genericComplexTypes);
            if (count($genericComplexTypes) > 1) {
                return new UnionType($genericComplexTypes);
            }
            return $genericComplexTypes[0];
        }
        if ($type instanceof ConstantArrayType) {
            return new ArrayType(new MixedType(), new MixedType());
        }
        // unclear
        return new MixedType();
    }
}
