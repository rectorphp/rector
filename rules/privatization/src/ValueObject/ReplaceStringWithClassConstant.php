<?php

declare(strict_types=1);

namespace Rector\Privatization\ValueObject;

final class ReplaceStringWithClassConstant
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
     * @var class-string
     */
    private $classWithConstants;

    /**
     * @var int
     */
    private $argPosition;

    /**
     * @param class-string $classWithConstants
     */
    public function __construct(string $class, string $method, int $argPosition, string $classWithConstants)
    {
        $this->class = $class;
        $this->method = $method;
        $this->classWithConstants = $classWithConstants;
        $this->argPosition = $argPosition;
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
     * @return class-string
     */
    public function getClassWithConstants(): string
    {
        return $this->classWithConstants;
    }

    public function getArgPosition(): int
    {
        return $this->argPosition;
    }
}
