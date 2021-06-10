<?php

declare(strict_types=1);

namespace Rector\Arguments\ValueObject;

use Rector\Arguments\Contract\ReplaceArgumentDefaultValueInterface;

final class ReplaceFuncCallArgumentDefaultValue implements ReplaceArgumentDefaultValueInterface
{
    /**
     * @param mixed $valueBefore
     * @param mixed $valueAfter
     */
    public function __construct(
        private string $function,
        private int $position,
        private $valueBefore,
        private $valueAfter
    ) {
    }

    public function getFunction(): string
    {
        return $this->function;
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
