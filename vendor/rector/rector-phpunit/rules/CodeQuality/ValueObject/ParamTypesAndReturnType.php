<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\ValueObject;

use PHPStan\Type\Type;
final class ParamTypesAndReturnType
{
    /**
     * @var Type[]
     * @readonly
     */
    private array $paramTypes;
    /**
     * @readonly
     */
    private ?Type $returnType;
    /**
     * @param Type[] $paramTypes
     */
    public function __construct(array $paramTypes, ?Type $returnType)
    {
        $this->paramTypes = $paramTypes;
        $this->returnType = $returnType;
    }
    /**
     * @return Type[]
     */
    public function getParamTypes() : array
    {
        return $this->paramTypes;
    }
    public function getReturnType() : ?Type
    {
        return $this->returnType;
    }
}
