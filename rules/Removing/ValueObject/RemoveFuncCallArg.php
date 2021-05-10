<?php

declare(strict_types=1);

namespace Rector\Removing\ValueObject;

final class RemoveFuncCallArg
{
    public function __construct(
        private string $function,
        private int $argumentPosition
    ) {
    }

    public function getFunction(): string
    {
        return $this->function;
    }

    public function getArgumentPosition(): int
    {
        return $this->argumentPosition;
    }
}
