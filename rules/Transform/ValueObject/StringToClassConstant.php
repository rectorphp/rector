<?php

declare(strict_types=1);

namespace Rector\Transform\ValueObject;

use Rector\Core\Validation\RectorAssert;

final class StringToClassConstant
{
    public function __construct(
        private readonly string $string,
        private readonly string $class,
        private readonly string $constant
    ) {
        RectorAssert::className($class);
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
