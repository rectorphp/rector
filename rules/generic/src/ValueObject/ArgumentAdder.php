<?php

declare(strict_types=1);

namespace Rector\Generic\ValueObject;

final class ArgumentAdder
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
     * @var string|null
     */
    private $argumentName;

    /**
     * @var mixed|null
     */
    private $argumentDefaultValue;

    /**
     * @var string|null
     */
    private $argumentType;

    /**
     * @var string|null
     */
    private $scope;

    /**
     * @param mixed|null $argumentDefaultValue
     */
    public function __construct(
        string $class,
        string $method,
        int $position,
        ?string $argumentName = null,
        $argumentDefaultValue = null,
        ?string $argumentType = null,
        ?string $scope = null
    ) {
        $this->class = $class;
        $this->method = $method;
        $this->position = $position;
        $this->argumentName = $argumentName;
        $this->argumentDefaultValue = $argumentDefaultValue;
        $this->argumentType = $argumentType;
        $this->scope = $scope;
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

    public function getArgumentName(): ?string
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

    public function getArgumentType(): ?string
    {
        return $this->argumentType;
    }

    public function getScope(): ?string
    {
        return $this->scope;
    }
}
