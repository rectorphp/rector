<?php

declare (strict_types=1);
namespace Rector\Arguments\ValueObject;

use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\Core\Validation\RectorAssert;
final class ArgumentAdder
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
     * @readonly
     * @var string|null
     */
    private $argumentName;
    /**
     * @var mixed|null
     */
    private $argumentDefaultValue = null;
    /**
     * @readonly
     * @var \PHPStan\Type\Type|null
     */
    private $argumentType = null;
    /**
     * @readonly
     * @var string|null
     */
    private $scope;
    /**
     * @param mixed|null $argumentDefaultValue
     * @param \PHPStan\Type\Type|null $argumentType
     */
    public function __construct(string $class, string $method, int $position, ?string $argumentName = null, $argumentDefaultValue = null, $argumentType = null, ?string $scope = null)
    {
        $this->class = $class;
        $this->method = $method;
        $this->position = $position;
        $this->argumentName = $argumentName;
        $this->argumentDefaultValue = $argumentDefaultValue;
        $this->argumentType = $argumentType;
        $this->scope = $scope;
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
    public function getArgumentName() : ?string
    {
        return $this->argumentName;
    }
    /**
     * @return mixed|null
     */
    public function getArgumentDefaultValue()
    {
        return $this->argumentDefaultValue;
    }
    public function getArgumentType() : ?\PHPStan\Type\Type
    {
        return $this->argumentType;
    }
    public function getScope() : ?string
    {
        return $this->scope;
    }
}
