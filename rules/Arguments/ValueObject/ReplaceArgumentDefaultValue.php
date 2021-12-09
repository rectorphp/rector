<?php

declare(strict_types=1);

namespace Rector\Arguments\ValueObject;

use PHPStan\Type\ObjectType;
use Rector\Arguments\Contract\ReplaceArgumentDefaultValueInterface;
use Rector\Core\Validation\RectorAssert;

final class ReplaceArgumentDefaultValue implements ReplaceArgumentDefaultValueInterface
{
    /**
     * @var string
     */
    final public const ANY_VALUE_BEFORE = '*ANY_VALUE_BEFORE*';

    /**
     * @param int<0, max> $position
     * @param mixed $valueBefore
     * @param mixed $valueAfter
     */
    public function __construct(
        private readonly string $class,
        private readonly string $method,
        private readonly int $position,
        private $valueBefore,
        private $valueAfter
    ) {
        RectorAssert::className($class);
    }

    public function getObjectType(): ObjectType
    {
        return new ObjectType($this->class);
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
