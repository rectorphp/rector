<?php

declare(strict_types=1);

namespace Rector\Transform\ValueObject;

use Rector\Core\Validation\RectorAssert;
use Rector\Transform\Contract\ValueObject\ArgumentFuncCallToMethodCallInterface;

final class ArrayFuncCallToMethodCall implements ArgumentFuncCallToMethodCallInterface
{
    /**
     * @param non-empty-string $function
     * @param non-empty-string $class
     * @param non-empty-string $arrayMethod
     * @param non-empty-string $nonArrayMethod
     */
    public function __construct(
        private readonly string $function,
        private readonly string $class,
        private readonly string $arrayMethod,
        private readonly string $nonArrayMethod
    ) {
        RectorAssert::className($class);
    }

    public function getFunction(): string
    {
        return $this->function;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getArrayMethod(): string
    {
        return $this->arrayMethod;
    }

    public function getNonArrayMethod(): string
    {
        return $this->nonArrayMethod;
    }
}
