<?php

declare (strict_types=1);
namespace Rector\Removing\ValueObject;

use PHPStan\Type\ObjectType;
use Rector\Core\Validation\RectorAssert;
final class ArgumentRemover
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
    private $method;
    /**
     * @readonly
     * @var int
     */
    private $position;
    /**
     * @readonly
     * @var mixed
     */
    private $value;
    /**
     * @param class-string $class
     * @param mixed $value
     */
    public function __construct(string $class, string $method, int $position, $value)
    {
        $this->class = $class;
        $this->method = $method;
        $this->position = $position;
        $this->value = $value;
        RectorAssert::className($class);
    }
    public function getObjectType() : ObjectType
    {
        return new ObjectType($this->class);
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
