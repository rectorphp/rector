<?php

declare (strict_types=1);
namespace Rector\Arguments\ValueObject;

use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\Validation\RectorAssert;
final class ArgumentAdder
{
    /**
     * @readonly
     */
    private string $class;
    /**
     * @readonly
     */
    private string $method;
    /**
     * @readonly
     */
    private int $position;
    /**
     * @readonly
     */
    private ?string $argumentName = null;
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
     */
    private ?string $scope = null;
    /**
     * @param mixed|null $argumentDefaultValue
     */
    public function __construct(string $class, string $method, int $position, ?string $argumentName = null, $argumentDefaultValue = null, ?\PHPStan\Type\Type $argumentType = null, ?string $scope = null)
    {
        $this->class = $class;
        $this->method = $method;
        $this->position = $position;
        $this->argumentName = $argumentName;
        $this->argumentDefaultValue = $argumentDefaultValue;
        $this->argumentType = $argumentType;
        $this->scope = $scope;
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
    public function getArgumentType() : ?Type
    {
        return $this->argumentType;
    }
    public function getScope() : ?string
    {
        return $this->scope;
    }
}
