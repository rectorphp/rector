<?php

declare(strict_types=1);

namespace Rector\Generics\TagValueNodeFactory;

use PHPStan\PhpDocParser\Ast\PhpDoc\MethodTagValueParameterNode;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Type\MixedType;
use Rector\StaticTypeMapper\StaticTypeMapper;

final class MethodTagValueParameterNodeFactory
{
    public function __construct(
        private StaticTypeMapper $staticTypeMapper
    ) {
    }

    public function createFromParamReflection(ParameterReflection $parameterReflection): MethodTagValueParameterNode
    {
        $parameterType = $parameterReflection->getType();
        if ($parameterType instanceof MixedType && ! $parameterType->isExplicitMixed()) {
            $parameterTypeNode = null;
        } else {
            $parameterTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode($parameterType);
        }

        return new MethodTagValueParameterNode(
            $parameterTypeNode,
            $parameterReflection->passedByReference()
                ->yes(),
            $parameterReflection->isVariadic(),
            '$' . $parameterReflection->getName(),
            // @todo resolve
            null
        );
    }
}
