<?php

declare (strict_types=1);
namespace Rector\Arguments\ValueObject;

use PHPStan\Type\ObjectType;
use Rector\Arguments\Contract\ReplaceArgumentDefaultValueInterface;
use Rector\Validation\RectorAssert;
final class ReplaceArgumentDefaultValue implements ReplaceArgumentDefaultValueInterface
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
     * @var int<0, max>
     * @readonly
     */
    private $position;
    /**
     * @readonly
     * @var mixed
     */
    private $valueBefore;
    /**
     * @readonly
     * @var mixed
     */
    private $valueAfter;
    /**
     * @var string
     */
    public const ANY_VALUE_BEFORE = '*ANY_VALUE_BEFORE*';
    /**
     * @param int<0, max> $position
     * @param mixed $valueBefore
     * @param mixed $valueAfter
     */
    public function __construct(string $class, string $method, int $position, $valueBefore, $valueAfter)
    {
        $this->class = $class;
        $this->method = $method;
        $this->position = $position;
        $this->valueBefore = $valueBefore;
        $this->valueAfter = $valueAfter;
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
    public function getValueBefore()
    {
        return $this->valueBefore;
    }
    /**
     * @return mixed
     */
    public function getValueAfter()
    {
        return $this->valueAfter;
    }
}
