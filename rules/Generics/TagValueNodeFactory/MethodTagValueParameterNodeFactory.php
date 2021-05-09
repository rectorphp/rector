<?php

declare (strict_types=1);
namespace Rector\Generics\TagValueNodeFactory;

use PHPStan\PhpDocParser\Ast\PhpDoc\MethodTagValueParameterNode;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Type\MixedType;
use Rector\StaticTypeMapper\StaticTypeMapper;
final class MethodTagValueParameterNodeFactory
{
    /**
     * @var StaticTypeMapper
     */
    private $staticTypeMapper;
    public function __construct(\Rector\StaticTypeMapper\StaticTypeMapper $staticTypeMapper)
    {
        $this->staticTypeMapper = $staticTypeMapper;
    }
    public function createFromParamReflection(\PHPStan\Reflection\ParameterReflection $parameterReflection) : \PHPStan\PhpDocParser\Ast\PhpDoc\MethodTagValueParameterNode
    {
        $parameterType = $parameterReflection->getType();
        if ($parameterType instanceof \PHPStan\Type\MixedType && !$parameterType->isExplicitMixed()) {
            $parameterTypeNode = null;
        } else {
            $parameterTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode($parameterType);
        }
        return new \PHPStan\PhpDocParser\Ast\PhpDoc\MethodTagValueParameterNode(
            $parameterTypeNode,
            $parameterReflection->passedByReference()->yes(),
            $parameterReflection->isVariadic(),
            '$' . $parameterReflection->getName(),
            // @todo resolve
            null
        );
    }
}
