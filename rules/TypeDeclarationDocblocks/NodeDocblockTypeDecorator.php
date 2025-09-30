<?php

declare (strict_types=1);
namespace Rector\TypeDeclarationDocblocks;

use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
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
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
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
    private DocBlockUpdater $docBlockUpdater;
    /**
     * @readonly
     */
    private PhpDocTypeChanger $phpDocTypeChanger;
    public function __construct(TypeNormalizer $typeNormalizer, StaticTypeMapper $staticTypeMapper, DocBlockUpdater $docBlockUpdater, PhpDocTypeChanger $phpDocTypeChanger)
    {
        $this->typeNormalizer = $typeNormalizer;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->docBlockUpdater = $docBlockUpdater;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
    }
    public function decorateGenericIterableParamType(Type $type, PhpDocInfo $phpDocInfo, ClassMethod $classMethod, Param $param, string $parameterName): bool
    {
        if ($this->isBareMixedType($type)) {
            // no value
            return \false;
        }
        $normalizedType = $this->typeNormalizer->generalizeConstantTypes($type);
        $typeNode = $this->createTypeNode($normalizedType);
        // no value iterable type
        if ($typeNode instanceof IdentifierTypeNode) {
            return \false;
        }
        $this->phpDocTypeChanger->changeParamType($classMethod, $phpDocInfo, $normalizedType, $param, $parameterName);
        return \true;
    }
    public function decorateGenericIterableReturnType(Type $type, PhpDocInfo $classMethodPhpDocInfo, ClassMethod $classMethod): bool
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
        $returnTagValueNode = new ReturnTagValueNode($typeNode, '');
        $this->addTagValueNodeAndUpdatePhpDocInfo($classMethodPhpDocInfo, $returnTagValueNode, $classMethod);
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
        $varTagValueNode = new VarTagValueNode($typeNode, '', '');
        $this->addTagValueNodeAndUpdatePhpDocInfo($phpDocInfo, $varTagValueNode, $property);
        return \true;
    }
    private function createTypeNode(Type $type): TypeNode
    {
        $generalizedReturnType = $this->typeNormalizer->generalizeConstantTypes($type);
        // turn into rather generic short return type, to keep it open to extension later and readable to human
        $typeNode = $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode($generalizedReturnType);
        if ($typeNode instanceof IdentifierTypeNode && $typeNode->name === 'mixed') {
            return new ArrayTypeNode($typeNode);
        }
        return $typeNode;
    }
    /**
     * @param \PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode|\PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode|\PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode $tagValueNode
     * @param \PhpParser\Node\Stmt\Property|\PhpParser\Node\Stmt\ClassMethod $stmt
     */
    private function addTagValueNodeAndUpdatePhpDocInfo(PhpDocInfo $phpDocInfo, $tagValueNode, $stmt): void
    {
        $phpDocInfo->addTagValueNode($tagValueNode);
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($stmt);
    }
    private function isBareMixedType(Type $type): bool
    {
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
