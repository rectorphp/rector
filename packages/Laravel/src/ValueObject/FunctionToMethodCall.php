<?php

declare(strict_types=1);

namespace Rector\Laravel\ValueObject;

final class FunctionToMethodCall
{
    /**
     * @var string
     */
    private $function;

    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $property;

    /**
     * @var string|null
     */
    private $methodIfNoArgs;

    /**
     * @var string|null
     */
    private $methodIfArgs;

    public function __construct(
        string $function, string $class, string $property, ?string $methodIfArgs = null, ?string $methodIfNoArgs = null
    ) {
        $this->function = $function;
        $this->class = $class;
        $this->property = $property;
        $this->methodIfArgs = $methodIfArgs;
        $this->methodIfNoArgs = $methodIfNoArgs;
    }

    public function getFunction(): string
    {
        return $this->function;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getProperty(): string
    {
        return $this->property;
    }

    public function getMethodIfNoArgs(): ?string
    {
        return $this->methodIfNoArgs;
    }

    public function getMethodIfArgs(): ?string
    {
        return $this->methodIfArgs;
    }
}
