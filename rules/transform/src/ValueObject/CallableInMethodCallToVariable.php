<?php

declare(strict_types=1);

namespace Rector\Transform\ValueObject;

final class CallableInMethodCallToVariable
{
    /**
     * @var string
     */
    private $classType;

    /**
     * @var string
     */
    private $methodName;

    /**
     * @var int
     */
    private $argumentPosition;

    public function __construct(string $classType, string $methodName, int $argumentPosition)
    {
        $this->classType = $classType;
        $this->methodName = $methodName;
        $this->argumentPosition = $argumentPosition;
    }

    public function getClassType(): string
    {
        return $this->classType;
    }

    public function getMethodName(): string
    {
        return $this->methodName;
    }

    public function getArgumentPosition(): int
    {
        return $this->argumentPosition;
    }
}
