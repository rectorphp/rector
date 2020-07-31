<?php

declare(strict_types=1);

namespace Rector\Decouple\ValueObject;

final class DecoupleClassMethodMatch
{
    /**
     * @var string
     */
    private $className;

    /**
     * @var string
     */
    private $methodName;

    /**
     * @var string|null
     */
    private $parentClassName;

    public function __construct(string $className, string $methodName, ?string $parentClassName = null)
    {
        $this->className = $className;
        $this->parentClassName = $parentClassName;
        $this->methodName = $methodName;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getParentClassName(): ?string
    {
        return $this->parentClassName;
    }

    public function getMethodName(): string
    {
        return $this->methodName;
    }
}
