<?php

declare (strict_types=1);
namespace Rector\Generics\TagValueNodeFactory;

use PHPStan\PhpDocParser\Ast\PhpDoc\MethodTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\MethodTagValueParameterNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Type\Generic\TemplateTypeMap;
use PHPStan\Type\Type;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Generics\ValueObject\ChildParentClassReflections;
use Rector\StaticTypeMapper\StaticTypeMapper;
final class MethodTagValueNodeFactory
{
    /**
     * @var MethodTagValueParameterNodeFactory
     */
    private $methodTagValueParameterNodeFactory;
    /**
     * @var StaticTypeMapper
     */
    private $staticTypeMapper;
    public function __construct(\Rector\StaticTypeMapper\StaticTypeMapper $staticTypeMapper, \Rector\Generics\TagValueNodeFactory\MethodTagValueParameterNodeFactory $methodTagValueParameterNodeFactory)
    {
        $this->methodTagValueParameterNodeFactory = $methodTagValueParameterNodeFactory;
        $this->staticTypeMapper = $staticTypeMapper;
    }
    public function createFromMethodReflectionAndReturnTagValueNode(\PHPStan\Reflection\MethodReflection $methodReflection, \PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode $returnTagValueNode, \Rector\Generics\ValueObject\ChildParentClassReflections $childParentClassReflections) : \PHPStan\PhpDocParser\Ast\PhpDoc\MethodTagValueNode
    {
        $templateTypeMap = $childParentClassReflections->getTemplateTypeMap();
        $returnTagTypeNode = $this->resolveReturnTagTypeNode($returnTagValueNode, $templateTypeMap);
        $parameterReflections = $methodReflection->getVariants()[0]->getParameters();
        $stringParameters = $this->resolveStringParameters($parameterReflections);
        return new \PHPStan\PhpDocParser\Ast\PhpDoc\MethodTagValueNode(\false, $returnTagTypeNode, $methodReflection->getName(), $stringParameters, '');
    }
    /**
     * @param ParameterReflection[] $parameterReflections
     * @return MethodTagValueParameterNode[]
     */
    private function resolveStringParameters(array $parameterReflections) : array
    {
        $stringParameters = [];
        foreach ($parameterReflections as $parameterReflection) {
            $stringParameters[] = $this->methodTagValueParameterNodeFactory->createFromParamReflection($parameterReflection);
        }
        return $stringParameters;
    }
    private function resolveReturnTagTypeNode(\PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode $returnTagValueNode, \PHPStan\Type\Generic\TemplateTypeMap $templateTypeMap) : \PHPStan\PhpDocParser\Ast\Type\TypeNode
    {
        $returnTagTypeNode = $returnTagValueNode->type;
        if ($returnTagValueNode->type instanceof \PHPStan\PhpDocParser\Ast\Type\UnionTypeNode) {
            return $this->resolveUnionTypeNode($returnTagValueNode->type, $templateTypeMap);
        }
        if ($returnTagValueNode->type instanceof \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode) {
            return $this->resolveIdentifierTypeNode($returnTagValueNode->type, $templateTypeMap, $returnTagTypeNode);
        }
        return $returnTagTypeNode;
    }
    private function resolveUnionTypeNode(\PHPStan\PhpDocParser\Ast\Type\UnionTypeNode $unionTypeNode, \PHPStan\Type\Generic\TemplateTypeMap $templateTypeMap) : \PHPStan\PhpDocParser\Ast\Type\UnionTypeNode
    {
        $resolvedTypes = [];
        foreach ($unionTypeNode->types as $unionedTypeNode) {
            if ($unionedTypeNode instanceof \PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode) {
                if (!$unionedTypeNode->type instanceof \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode) {
                    throw new \Rector\Core\Exception\ShouldNotHappenException();
                }
                $resolvedType = $this->resolveIdentifierTypeNode($unionedTypeNode->type, $templateTypeMap, $unionedTypeNode);
                $resolvedTypes[] = new \PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode($resolvedType);
            } elseif ($unionedTypeNode instanceof \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode) {
                $resolvedTypes[] = $this->resolveIdentifierTypeNode($unionedTypeNode, $templateTypeMap, $unionedTypeNode);
            }
        }
        return new \PHPStan\PhpDocParser\Ast\Type\UnionTypeNode($resolvedTypes);
    }
    private function resolveIdentifierTypeNode(\PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode $identifierTypeNode, \PHPStan\Type\Generic\TemplateTypeMap $templateTypeMap, \PHPStan\PhpDocParser\Ast\Type\TypeNode $fallbackTypeNode) : \PHPStan\PhpDocParser\Ast\Type\TypeNode
    {
        $typeName = $identifierTypeNode->name;
        $genericType = $templateTypeMap->getType($typeName);
        if ($genericType instanceof \PHPStan\Type\Type) {
            return $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode($genericType);
        }
        return $fallbackTypeNode;
    }
}
