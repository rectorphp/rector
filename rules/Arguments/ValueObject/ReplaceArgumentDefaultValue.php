<?php

declare (strict_types=1);
namespace Rector\Arguments\ValueObject;

use PHPStan\Type\ObjectType;
use Rector\Arguments\Contract\ReplaceArgumentDefaultValueInterface;
final class ReplaceArgumentDefaultValue implements \Rector\Arguments\Contract\ReplaceArgumentDefaultValueInterface
{
    /**
     * @var string
     */
    public const ANY_VALUE_BEFORE = '*ANY_VALUE_BEFORE*';
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
    private $valueBefore;
    private $valueAfter;
    /**
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
