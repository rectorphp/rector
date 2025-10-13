<?php

declare (strict_types=1);
namespace Rector\TypeDeclarationDocblocks;

use PhpParser\Node\FunctionLike;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\ArrayType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NeverType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Privatization\TypeManipulator\TypeNormalizer;
use Rector\StaticTypeMapper\StaticTypeMapper;
final class NodeDocblockTypeDecorator
{
    /**
     * @readonly
     */
    private TypeNormalizer $typeNormalizer;
    /**
     * @readonly
     */
    private StaticTypeMapper $staticTypeMapper;
    /**
     * @readonly
     */
    private PhpDocTypeChanger $phpDocTypeChanger;
    public function __construct(TypeNormalizer $typeNormalizer, StaticTypeMapper $staticTypeMapper, PhpDocTypeChanger $phpDocTypeChanger)
    {
        $this->typeNormalizer = $typeNormalizer;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
    }
    public function decorateGenericIterableParamType(Type $type, PhpDocInfo $phpDocInfo, FunctionLike $functionLike, Param $param, string $parameterName): bool
    {
        if ($this->isBareMixedType($type)) {
            // no value
            return \false;
        }
        $typeNode = $this->createTypeNode($type);
        // no value iterable type
        if ($typeNode instanceof IdentifierTypeNode) {
            return \false;
        }
        $this->phpDocTypeChanger->changeParamTypeNode($functionLike, $phpDocInfo, $param, $parameterName, $typeNode);
        return \true;
    }
    /**
     * @param \PHPStan\Type\Type|\PHPStan\PhpDocParser\Ast\Type\TypeNode $typeOrTypeNode
     */
    public function decorateGenericIterableReturnType($typeOrTypeNode, PhpDocInfo $classMethodPhpDocInfo, FunctionLike $functionLike): bool
    {
        if ($typeOrTypeNode instanceof TypeNode) {
            $type = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($typeOrTypeNode, $functionLike);
        } else {
            $type = $typeOrTypeNode;
        }
        if ($this->isBareMixedType($type)) {
            // no value
            return \false;
        }
        if ($typeOrTypeNode instanceof TypeNode) {
            $typeNode = $typeOrTypeNode;
        } else {
            $typeNode = $this->createTypeNode($typeOrTypeNode);
        }
        // no value iterable type
        if ($typeNode instanceof IdentifierTypeNode) {
            return \false;
        }
        $this->phpDocTypeChanger->changeReturnTypeNode($functionLike, $classMethodPhpDocInfo, $typeNode);
        return \true;
    }
    public function decorateGenericIterableVarType(Type $type, PhpDocInfo $phpDocInfo, Property $property): bool
    {
        $typeNode = $this->createTypeNode($type);
        if ($this->isBareMixedType($type)) {
            // no value
            return \false;
        }
        // no value iterable type
        if ($typeNode instanceof IdentifierTypeNode) {
            return \false;
        }
        $this->phpDocTypeChanger->changeVarTypeNode($property, $phpDocInfo, $typeNode);
        return \true;
    }
    private function createTypeNode(Type $type): TypeNode
    {
        $generalizedType = $this->typeNormalizer->generalizeConstantTypes($type);
        // turn into rather generic short return typeOrTypeNode, to keep it open to extension later and readable to human
        $typeNode = $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode($generalizedType);
        if ($typeNode instanceof IdentifierTypeNode && $typeNode->name === 'mixed') {
            return new ArrayTypeNode($typeNode);
        }
        return $typeNode;
    }
    private function isBareMixedType(Type $type): bool
    {
        if ($type instanceof MixedType) {
            return \true;
        }
        $normalizedResolvedParameterType = $this->typeNormalizer->generalizeConstantTypes($type);
        // most likely mixed, skip
        return $this->isArrayMixed($normalizedResolvedParameterType);
    }
    private function isArrayMixed(Type $type): bool
    {
        if (!$type instanceof ArrayType) {
            return \false;
        }
        if ($type->getItemType() instanceof NeverType) {
            return \true;
        }
        if (!$type->getItemType() instanceof MixedType) {
            return \false;
        }
        return $type->getKeyType() instanceof IntegerType;
    }
}
