<?php

declare(strict_types=1);

namespace Rector\Generics\NodeType;

use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\MethodTagValueNode;
use PHPStan\Type\Generic\TemplateTypeHelper;
use PHPStan\Type\Generic\TemplateTypeMap;
use Rector\StaticTypeMapper\StaticTypeMapper;

final class GenericTypeSpecifier
{
    /**
     * @var StaticTypeMapper
     */
    private $staticTypeMapper;

    public function __construct(StaticTypeMapper $staticTypeMapper)
    {
        $this->staticTypeMapper = $staticTypeMapper;
    }

    /**
     * @param MethodTagValueNode[] $methodTagValueNodes
     */
    public function replaceGenericTypesWithSpecificTypes(
        array $methodTagValueNodes,
        Node $node,
        TemplateTypeMap $templateTypeMap
    ): void {
        foreach ($methodTagValueNodes as $methodTagValueNode) {
            if ($methodTagValueNode->returnType === null) {
                continue;
            }

            $returnType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType(
                $methodTagValueNode->returnType,
                $node
            );

            $resolvedType = TemplateTypeHelper::resolveTemplateTypes($returnType, $templateTypeMap);
            $resolvedTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode($resolvedType);
            $methodTagValueNode->returnType = $resolvedTypeNode;
        }
    }
}
