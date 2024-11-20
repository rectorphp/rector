<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\ValueObject;

use PHPStan\Type\Type;
use Rector\Validation\RectorAssert;
final class AddPropertyTypeDeclaration
{
    /**
     * @readonly
     */
    private string $class;
    /**
     * @readonly
     */
    private string $propertyName;
    /**
     * @readonly
     */
    private Type $type;
    public function __construct(string $class, string $propertyName, Type $type)
    {
        $this->class = $class;
        $this->propertyName = $propertyName;
        $this->type = $type;
        RectorAssert::className($class);
    }
    public function getClass() : string
    {
        return $this->class;
    }
    public function getPropertyName() : string
    {
        return $this->propertyName;
    }
    public function getType() : Type
    {
        return $this->type;
    }
}
