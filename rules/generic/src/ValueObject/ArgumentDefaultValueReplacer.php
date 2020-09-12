<?php

declare(strict_types=1);

namespace Rector\Generic\ValueObject;

final class ArgumentDefaultValueReplacer
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

    /**
     * @var mixed
     */
    private $valueBefore;

    /**
     * @var mixed
     */
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

    public function getClass(): string
    {
        return $this->class;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getPosition(): int
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
