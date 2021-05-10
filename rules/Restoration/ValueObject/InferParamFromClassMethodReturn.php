<?php

declare(strict_types=1);

namespace Rector\Restoration\ValueObject;

final class InferParamFromClassMethodReturn
{
    public function __construct(
        private string $class,
        private string $paramMethod,
        private string $returnMethod
    ) {
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getParamMethod(): string
    {
        return $this->paramMethod;
    }

    public function getReturnMethod(): string
    {
        return $this->returnMethod;
    }
}
