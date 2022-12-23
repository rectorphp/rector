<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\ValueObject;

use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\Core\Validation\RectorAssert;
/**
 * @api
 */
final class AddReturnTypeDeclaration
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
    private $method;
    /**
     * @readonly
     * @var \PHPStan\Type\Type
     */
    private $returnType;
    public function __construct(string $class, string $method, Type $returnType)
    {
        $this->class = $class;
        $this->method = $method;
        $this->returnType = $returnType;
        RectorAssert::className($class);
    }
    public function getClass() : string
    {
        return $this->class;
    }
    public function getMethod() : string
    {
        return $this->method;
    }
    public function getReturnType() : Type
    {
        return $this->returnType;
    }
    public function getObjectType() : ObjectType
    {
        return new ObjectType($this->class);
    }
}
