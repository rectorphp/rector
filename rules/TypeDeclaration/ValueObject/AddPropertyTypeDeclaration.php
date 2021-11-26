<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\ValueObject;

use PHPStan\Type\Type;
final class AddPropertyTypeDeclaration
{
    /**
     * @var string
     */
    private $class;
    /**
     * @var string
     */
    private $propertyName;
    /**
     * @var \PHPStan\Type\Type
     */
    private $type;
    public function __construct(string $class, string $propertyName, \PHPStan\Type\Type $type)
    {
        $this->class = $class;
        $this->propertyName = $propertyName;
        $this->type = $type;
    }
    public function getClass() : string
    {
        return $this->class;
    }
    public function getPropertyName() : string
    {
        return $this->propertyName;
    }
    public function getType() : \PHPStan\Type\Type
    {
        return $this->type;
    }
}
