<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\ValueObject;

use PHPStan\Type\Type;
use Rector\Validation\RectorAssert;
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
