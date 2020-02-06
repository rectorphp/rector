<?php

declare(strict_types=1);

namespace Rector\Core\ValueObject\Scan;

final class ClassMethodWithArguments
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
     * @var string
     */
    private $returnType;

    /**
     * @var Argument[]
     */
    private $arguments = [];

    /**
     * @param Argument[] $arguments
     */
    public function __construct(string $class, string $method, array $arguments, string $returnType)
    {
        $this->class = $class;
        $this->method = $method;
        $this->arguments = $arguments;
        $this->returnType = $returnType;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return Argument[]
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function getArgumentByPosition(int $position): ?Argument
    {
        foreach ($this->arguments as $argument) {
            if ($argument->getPosition() !== $position) {
                continue;
            }

            return $argument;
        }

        return null;
    }

    public function getReturnType(): string
    {
        return $this->returnType;
    }
}
