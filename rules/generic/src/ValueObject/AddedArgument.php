<?php

declare(strict_types=1);

namespace Rector\Generic\ValueObject;

final class AddedArgument
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
     * @var string
     */
    private $argumentName;

    /**
     * @var string|null
     */
    private $argumentDefaultValue;

    /**
     * @var string|null
     */
    private $argumentType;

    /**
     * @var string[]
     */
    private $scope = [];

    public function __construct(
        string $class,
        string $method,
        int $position,
        string $argumentName,
        ?string $argumentDefaultValue = null,
        ?string $argumentType = null,
        array $scope = []
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

    public function getArgumentName(): string
    {
        return $this->argumentName;
    }

    public function getArgumentDefaultValue(): ?string
    {
        return $this->argumentDefaultValue;
    }

    public function getArgumentType(): ?string
    {
        return $this->argumentType;
    }

    /**
     * @return string[]
     */
    public function getScope(): array
    {
        return $this->scope;
    }
}
