<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node\Identifier;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\ArrayType;
use PHPStan\Type\ClassStringType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Generic\GenericClassStringType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NeverType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareUnionTypeNode;
use Rector\BetterPhpDocParser\ValueObject\Type\SpacingAwareArrayTypeNode;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper;
use Rector\TypeDeclaration\NodeTypeAnalyzer\DetailedTypeAnalyzer;
use Rector\TypeDeclaration\TypeAnalyzer\GenericClassStringTypeNormalizer;
/**
 * @see \Rector\Tests\PHPStanStaticTypeMapper\TypeMapper\ArrayTypeMapperTest
 *
 * @implements TypeMapperInterface<ArrayType>
 */
final class ArrayTypeMapper implements TypeMapperInterface
{
    /**
     * @readonly
     */
    private GenericClassStringTypeNormalizer $genericClassStringTypeNormalizer;
    /**
     * @readonly
     */
    private DetailedTypeAnalyzer $detailedTypeAnalyzer;
    /**
     * @var string
     */
    public const HAS_GENERIC_TYPE_PARENT = 'has_generic_type_parent';
    private PHPStanStaticTypeMapper $phpStanStaticTypeMapper;
    public function __construct(GenericClassStringTypeNormalizer $genericClassStringTypeNormalizer, DetailedTypeAnalyzer $detailedTypeAnalyzer)
    {
        $this->genericClassStringTypeNormalizer = $genericClassStringTypeNormalizer;
        $this->detailedTypeAnalyzer = $detailedTypeAnalyzer;
    }
    // To avoid circular dependency
    public function autowire(PHPStanStaticTypeMapper $phpStanStaticTypeMapper) : void
    {
        $this->phpStanStaticTypeMapper = $phpStanStaticTypeMapper;
    }
    public function getNodeClass() : string
    {
        return ArrayType::class;
    }
    /**
     * @param ArrayType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type) : TypeNode
    {
        // this cannot be handled by PHPStan $type->toPhpDocNode() as requires space removal around "|" in union type
        // then e.g. "int" instead of explicit number, and nice arrays
        $itemType = $type->getIterableValueType();
        $isGenericArray = $this->isGenericArrayCandidate($type);
        if ($itemType instanceof UnionType && !$type instanceof ConstantArrayType && !$isGenericArray) {
            return $this->createArrayTypeNodeFromUnionType($itemType);
        }
        if ($itemType instanceof ArrayType && $this->isGenericArrayCandidate($itemType)) {
            return $this->createGenericArrayType($type, \true);
        }
        if ($isGenericArray) {
            return $this->createGenericArrayType($type, \true);
        }
        $itemTypeNode = $this->phpStanStaticTypeMapper->mapToPHPStanPhpDocTypeNode($itemType);
        return new SpacingAwareArrayTypeNode($itemTypeNode);
    }
    /**
     * @param ArrayType $type
     */
    public function mapToPhpParserNode(Type $type, string $typeKind) : Identifier
    {
        return new Identifier('array');
    }
    private function createArrayTypeNodeFromUnionType(UnionType $unionType) : SpacingAwareArrayTypeNode
    {
        $unionedArrayType = [];
        foreach ($unionType->getTypes() as $unionedType) {
            $typeNode = $this->phpStanStaticTypeMapper->mapToPHPStanPhpDocTypeNode($unionedType);
            $unionedArrayType[(string) $typeNode] = $typeNode;
        }
        if (\count($unionedArrayType) > 1) {
            return new SpacingAwareArrayTypeNode(new BracketsAwareUnionTypeNode($unionedArrayType));
        }
        /** @var TypeNode $arrayType */
        $arrayType = \array_shift($unionedArrayType);
        return new SpacingAwareArrayTypeNode($arrayType);
    }
    private function isGenericArrayCandidate(ArrayType $arrayType) : bool
    {
        if ($arrayType->getKeyType() instanceof MixedType) {
            return \false;
        }
        if ($this->isClassStringArrayType($arrayType)) {
            return \true;
        }
        // skip simple arrays, like "string[]", from converting to obvious "array<int, string>"
        if ($this->isIntegerKeyAndNonNestedArray($arrayType)) {
            return \false;
        }
        if ($arrayType->getKeyType() instanceof NeverType) {
            return \false;
        }
        // make sure the integer key type is not natural/implicit array int keys
        $keysArrayType = $arrayType->getKeysArray();
        if (!$keysArrayType instanceof ConstantArrayType) {
            return \true;
        }
        foreach ($keysArrayType->getValueTypes() as $key => $keyType) {
            if (!$keyType instanceof ConstantIntegerType) {
                return \true;
            }
            if ($key !== $keyType->getValue()) {
                return \true;
            }
        }
        return \false;
    }
    private function createGenericArrayType(ArrayType $arrayType, bool $withKey = \false) : GenericTypeNode
    {
        $itemType = $arrayType->getIterableValueType();
        $itemTypeNode = $this->phpStanStaticTypeMapper->mapToPHPStanPhpDocTypeNode($itemType);
        $identifierTypeNode = new IdentifierTypeNode('array');
        // is class-string[] list only
        if ($this->isClassStringArrayType($arrayType)) {
            $withKey = \false;
        }
        if ($withKey) {
            $keyTypeNode = $this->phpStanStaticTypeMapper->mapToPHPStanPhpDocTypeNode($arrayType->getKeyType());
            if ($itemTypeNode instanceof BracketsAwareUnionTypeNode && $this->isPairClassTooDetailed($itemType)) {
                $genericTypes = [$keyTypeNode, $this->phpStanStaticTypeMapper->mapToPHPStanPhpDocTypeNode(new ClassStringType())];
            } else {
                $genericTypes = [$keyTypeNode, $itemTypeNode];
            }
        } else {
            $genericTypes = [$itemTypeNode];
        }
        // @see https://github.com/phpstan/phpdoc-parser/blob/98a088b17966bdf6ee25c8a4b634df313d8aa531/tests/PHPStan/Parser/PhpDocParserTest.php#L2692-L2696
        foreach ($genericTypes as $genericType) {
            /** @var TypeNode $genericType */
            $genericType->setAttribute(self::HAS_GENERIC_TYPE_PARENT, $withKey);
        }
        $identifierTypeNode->setAttribute(self::HAS_GENERIC_TYPE_PARENT, $withKey);
        return new GenericTypeNode($identifierTypeNode, $genericTypes);
    }
    private function isPairClassTooDetailed(Type $itemType) : bool
    {
        if (!$itemType instanceof UnionType) {
            return \false;
        }
        if (!$this->genericClassStringTypeNormalizer->isAllGenericClassStringType($itemType)) {
            return \false;
        }
        return $this->detailedTypeAnalyzer->isTooDetailed($itemType);
    }
    private function isIntegerKeyAndNonNestedArray(ArrayType $arrayType) : bool
    {
        if (!$arrayType->getKeyType()->isInteger()->yes()) {
            return \false;
        }
        return !$arrayType->getIterableValueType()->isArray()->yes();
    }
    private function isClassStringArrayType(ArrayType $arrayType) : bool
    {
        if ($arrayType->getKeyType() instanceof MixedType) {
            return $arrayType->getIterableValueType() instanceof GenericClassStringType;
        }
        if ($arrayType->getKeyType() instanceof ConstantIntegerType) {
            return $arrayType->getIterableValueType() instanceof GenericClassStringType;
        }
        return \false;
    }
}
