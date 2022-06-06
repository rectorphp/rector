<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\TypeDeclaration\ValueObject;

use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\Rector\Core\Validation\RectorAssert;
final class AddPropertyTypeDeclaration
{
    /**
     * @readonly
     * @var string
     */
    private $class;
    /**
     * @readonly
     * @var string
     */
    private $propertyName;
    /**
     * @readonly
     * @var \PHPStan\Type\Type
     */
    private $type;
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
