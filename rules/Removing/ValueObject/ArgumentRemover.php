<?php

declare (strict_types=1);
namespace Rector\Removing\ValueObject;

use PHPStan\Type\ObjectType;
final class ArgumentRemover
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
    private $value;
    /**
     * @param mixed $value
     */
    public function __construct(string $class, string $method, int $position, $value)
    {
        $this->class = $class;
        $this->method = $method;
        $this->position = $position;
        $this->value = $value;
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
    public function getValue()
    {
        return $this->value;
    }
}
