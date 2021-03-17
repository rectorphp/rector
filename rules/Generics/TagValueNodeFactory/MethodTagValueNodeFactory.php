<?php

declare(strict_types=1);

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

    public function __construct(
        StaticTypeMapper $staticTypeMapper,
        MethodTagValueParameterNodeFactory $methodTagValueParameterNodeFactory
    ) {
        $this->methodTagValueParameterNodeFactory = $methodTagValueParameterNodeFactory;
        $this->staticTypeMapper = $staticTypeMapper;
    }

    public function createFromMethodReflectionAndReturnTagValueNode(
        MethodReflection $methodReflection,
        ReturnTagValueNode $returnTagValueNode,
        ChildParentClassReflections $childParentClassReflections
    ): MethodTagValueNode {
        $templateTypeMap = $childParentClassReflections->getTemplateTypeMap();
        $returnTagTypeNode = $this->resolveReturnTagTypeNode($returnTagValueNode, $templateTypeMap);

        $parameterReflections = $methodReflection->getVariants()[0]
            ->getParameters();
        $stringParameters = $this->resolveStringParameters($parameterReflections);

        return new MethodTagValueNode(
            false,
            $returnTagTypeNode,
            $methodReflection->getName(),
            $stringParameters,
            ''
        );
    }

    /**
     * @param ParameterReflection[] $parameterReflections
     * @return MethodTagValueParameterNode[]
     */
    private function resolveStringParameters(array $parameterReflections): array
    {
        $stringParameters = [];

        foreach ($parameterReflections as $parameterReflection) {
            $stringParameters[] = $this->methodTagValueParameterNodeFactory->createFromParamReflection(
                $parameterReflection
            );
        }

        return $stringParameters;
    }

    private function resolveReturnTagTypeNode(
        ReturnTagValueNode $returnTagValueNode,
        TemplateTypeMap $templateTypeMap
    ): TypeNode {
        $returnTagTypeNode = $returnTagValueNode->type;
        if ($returnTagValueNode->type instanceof UnionTypeNode) {
            return $this->resolveUnionTypeNode($returnTagValueNode->type, $templateTypeMap);
        }

        if ($returnTagValueNode->type instanceof IdentifierTypeNode) {
            return $this->resolveIdentifierTypeNode(
                $returnTagValueNode->type,
                $templateTypeMap,
                $returnTagTypeNode
            );
        }

        return $returnTagTypeNode;
    }

    private function resolveUnionTypeNode(UnionTypeNode $unionTypeNode, TemplateTypeMap $templateTypeMap): UnionTypeNode
    {
        $resolvedTypes = [];
        foreach ($unionTypeNode->types as $unionedTypeNode) {
            if ($unionedTypeNode instanceof ArrayTypeNode) {
                if (! $unionedTypeNode->type instanceof IdentifierTypeNode) {
                    throw new ShouldNotHappenException();
                }

                $resolvedType = $this->resolveIdentifierTypeNode(
                    $unionedTypeNode->type,
                    $templateTypeMap,
                    $unionedTypeNode
                );

                $resolvedTypes[] = new ArrayTypeNode($resolvedType);
            } elseif ($unionedTypeNode instanceof IdentifierTypeNode) {
                $resolvedTypes[] = $this->resolveIdentifierTypeNode(
                    $unionedTypeNode,
                    $templateTypeMap,
                    $unionedTypeNode
                );
            }
        }

        return new UnionTypeNode($resolvedTypes);
    }

    private function resolveIdentifierTypeNode(
        IdentifierTypeNode $identifierTypeNode,
        TemplateTypeMap $templateTypeMap,
        TypeNode $fallbackTypeNode
    ): TypeNode {
        $typeName = $identifierTypeNode->name;
        $genericType = $templateTypeMap->getType($typeName);

        if ($genericType instanceof Type) {
            return $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode($genericType);
        }

        return $fallbackTypeNode;
    }
}
