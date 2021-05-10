<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\ValueObject;

use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
final class AddReturnTypeDeclaration
{
    /**
     * @var string
     */
    private $class;
    /**
     * @var string
     */
    private $method;
    /**
     * @var \PHPStan\Type\Type
     */
    private $returnType;
    public function __construct(string $class, string $method, \PHPStan\Type\Type $returnType)
    {
        $this->class = $class;
        $this->method = $method;
        $this->returnType = $returnType;
    }
    public function getClass() : string
    {
        return $this->class;
    }
    public function getMethod() : string
    {
        return $this->method;
    }
    public function getReturnType() : \PHPStan\Type\Type
    {
        return $this->returnType;
    }
    public function getObjectType() : \PHPStan\Type\ObjectType
    {
        return new \PHPStan\Type\ObjectType($this->class);
    }
}
