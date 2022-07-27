<?php

declare (strict_types=1);
namespace Rector\Arguments\ValueObject;

use Rector\Arguments\Contract\ReplaceArgumentDefaultValueInterface;
final class ReplaceFuncCallArgumentDefaultValue implements ReplaceArgumentDefaultValueInterface
{
    /**
     * @readonly
     * @var string
     */
    private $function;
    /**
     * @readonly
     * @var int
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
     * @param mixed $valueBefore
     * @param mixed $valueAfter
     */
    public function __construct(string $function, int $position, $valueBefore, $valueAfter)
    {
        $this->function = $function;
        $this->position = $position;
        $this->valueBefore = $valueBefore;
        $this->valueAfter = $valueAfter;
    }
    public function getFunction() : string
    {
        return $this->function;
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
