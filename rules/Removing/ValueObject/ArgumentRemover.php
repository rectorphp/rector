<?php

declare (strict_types=1);
namespace Rector\Removing\ValueObject;

use PHPStan\Type\ObjectType;
use Rector\Core\Validation\RectorAssert;
final class ArgumentRemover
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
     * @var int
     */
    private $position;
    /**
     * @var mixed
     */
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
        \Rector\Core\Validation\RectorAssert::className($class);
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
