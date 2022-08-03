<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\ValueObject;

use PHPStan\Type\Type;
use Rector\Core\Validation\RectorAssert;
final class AddPropertyTypeDeclaration
{
    /**
     * @var class-string
     * @readonly
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
    /**
     * @param class-string $class
     */
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
