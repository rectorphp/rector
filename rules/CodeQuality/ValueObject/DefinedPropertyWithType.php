<?php

declare (strict_types=1);
namespace Rector\CodeQuality\ValueObject;

use PHPStan\Type\Type;
final class DefinedPropertyWithType
{
    /**
     * @readonly
     */
    private string $propertyName;
    /**
     * @readonly
     */
    private Type $type;
    /**
     * @readonly
     */
    private ?string $definedInMethodName;
    public function __construct(string $propertyName, Type $type, ?string $definedInMethodName)
    {
        $this->propertyName = $propertyName;
        $this->type = $type;
        $this->definedInMethodName = $definedInMethodName;
    }
    public function getName(): string
    {
        return $this->propertyName;
    }
    public function getType(): Type
    {
        return $this->type;
    }
    public function getDefinedInMethodName(): ?string
    {
        return $this->definedInMethodName;
    }
}
