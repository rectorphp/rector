<?php

declare(strict_types=1);

namespace Rector\Generics\TagValueNodeFactory;

use PHPStan\PhpDocParser\Ast\PhpDoc\MethodTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\MethodTagValueParameterNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Type\Type;
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
        ReturnTagValueNode $returnTagValueNode
    ): MethodTagValueNode {
        $parameterReflections = $methodReflection->getVariants()[0]
            ->getParameters();

        $stringParameters = $this->resolveStringParameters($parameterReflections);

        $classReflection = $methodReflection->getDeclaringClass();
        $templateTypeMap = $classReflection->getTemplateTypeMap();

        $returnTagTypeNode = $returnTagValueNode->type;

        if ($returnTagValueNode->type instanceof IdentifierTypeNode) {
            $typeName = $returnTagValueNode->type->name;
            $genericType = $templateTypeMap->getType($typeName);

            if ($genericType instanceof Type) {
                $returnTagType = $genericType;
                $returnTagTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode($returnTagType);
            }
        }

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
}
