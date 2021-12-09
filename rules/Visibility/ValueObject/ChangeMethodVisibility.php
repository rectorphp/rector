<?php

declare(strict_types=1);

namespace Rector\Visibility\ValueObject;

use Rector\Core\Validation\RectorAssert;

final class ChangeMethodVisibility
{
    public function __construct(
        private readonly string $class,
        private readonly string $method,
        private readonly int $visibility
    ) {
        RectorAssert::className($class);
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getVisibility(): int
    {
        return $this->visibility;
    }
}
