<?php

declare(strict_types=1);

namespace Rector\Generics\NodeType;

use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\MethodTagValueNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\Generic\TemplateTypeHelper;
use PHPStan\Type\Generic\TemplateTypeMap;
use Rector\StaticTypeMapper\StaticTypeMapper;

final class GenericTypeSpecifier
{
    /**
     * @var StaticTypeMapper
     */
    private $staticTypeMapper;

    /**
     * @var ExtendsTemplateTypeMapFallbackFactory
     */
    private $extendsTemplateTypeMapFallbackFactory;

    public function __construct(
        StaticTypeMapper $staticTypeMapper,
        ExtendsTemplateTypeMapFallbackFactory $extendsTemplateTypeMapFallbackFactory
    ) {
        $this->staticTypeMapper = $staticTypeMapper;
        $this->extendsTemplateTypeMapFallbackFactory = $extendsTemplateTypeMapFallbackFactory;
    }

    /**
     * @param MethodTagValueNode[] $methodTagValueNodes
     */
    public function replaceGenericTypesWithSpecificTypes(
        array $methodTagValueNodes,
        Node $node,
        ClassReflection $classReflection
    ): void {
        $templateTypeMap = $this->resolveAvailableTemplateTypeMap($classReflection);
        foreach ($methodTagValueNodes as $methodTagValueNode) {
            if ($methodTagValueNode->returnType === null) {
                continue;
            }

            $returnType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanTypeWithTemplateTypeMap(
                $methodTagValueNode->returnType,
                $node,
                $templateTypeMap
            );

            $resolvedType = TemplateTypeHelper::resolveTemplateTypes($returnType, $templateTypeMap);
            $resolvedTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode($resolvedType);
            $methodTagValueNode->returnType = $resolvedTypeNode;
        }
    }

    private function resolveAvailableTemplateTypeMap(ClassReflection $classReflection): TemplateTypeMap
    {
        $templateTypeMap = $classReflection->getTemplateTypeMap();

        // add template map from extends
        if ($templateTypeMap->getTypes() !== []) {
            return $templateTypeMap;
        }
        $fallbackTemplateTypeMap = $this->extendsTemplateTypeMapFallbackFactory->createFromClassReflection(
            $classReflection
        );

        if ($fallbackTemplateTypeMap instanceof TemplateTypeMap) {
            return $fallbackTemplateTypeMap;
        }

        return $templateTypeMap;
    }
}
