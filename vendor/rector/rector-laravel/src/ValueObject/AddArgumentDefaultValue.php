<?php

declare (strict_types=1);
namespace Rector\Laravel\ValueObject;

use PHPStan\Type\ObjectType;
final class AddArgumentDefaultValue
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
     * @var int
     */
    private $position;
    private $defaultValue;
    /**
     * @param mixed $defaultValue
     */
    public function __construct(string $class, string $method, int $position, $defaultValue)
    {
        $this->class = $class;
        $this->method = $method;
        $this->position = $position;
        $this->defaultValue = $defaultValue;
    }
    public function getObjectType() : \PHPStan\Type\ObjectType
    {
        return new \PHPStan\Type\ObjectType($this->class);
    }
    public function getMethod() : string
    {
        return $this->method;
    }
    public function getPosition() : int
    {
        return $this->position;
    }
    /**
     * @return mixed
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }
}
