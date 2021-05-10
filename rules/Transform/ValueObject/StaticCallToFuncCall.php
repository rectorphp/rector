<?php

declare (strict_types=1);
namespace Rector\Transform\ValueObject;

use PHPStan\Type\ObjectType;
final class StaticCallToFuncCall
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
     * @var string
     */
    private $function;
    public function __construct(string $class, string $method, string $function)
    {
        $this->class = $class;
        $this->method = $method;
        $this->function = $function;
    }
    public function getObjectType() : \PHPStan\Type\ObjectType
    {
        return new \PHPStan\Type\ObjectType($this->class);
    }
    public function getMethod() : string
    {
        return $this->method;
    }
    public function getFunction() : string
    {
        return $this->function;
    }
}
