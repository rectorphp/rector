<?php

declare(strict_types=1);

namespace Rector\Transform\ValueObject;

final class StringToClassConstant
{
    public function __construct(
        private string $string,
        private string $class,
        private string $constant
    ) {
    }

    public function getString(): string
    {
        return $this->string;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getConstant(): string
    {
        return $this->constant;
    }
}
