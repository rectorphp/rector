<?php

declare(strict_types=1);

namespace Rector\Generic\ValueObject;

final class NormalToFluent
{
    /**
     * @var string
     */
    private $class;

    /**
     * @var string[]
     */
    private $methodNames = [];

    /**
     * @param string[] $methodNames
     */
    public function __construct(string $class, array $methodNames)
    {
        $this->class = $class;
        $this->methodNames = $methodNames;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * @return string[]
     */
    public function getMethodNames(): array
    {
        return $this->methodNames;
    }
}
