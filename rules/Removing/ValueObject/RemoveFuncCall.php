<?php

declare(strict_types=1);

namespace Rector\Removing\ValueObject;

final class RemoveFuncCall
{
    /**
     * @param array<int, mixed[]> $argumentPositionAndValues
     */
    public function __construct(
        private string $funcCall,
        private array $argumentPositionAndValues = []
    ) {
    }

    public function getFuncCall(): string
    {
        return $this->funcCall;
    }

    /**
     * @return array<int, mixed[]>
     */
    public function getArgumentPositionAndValues(): array
    {
        return $this->argumentPositionAndValues;
    }
}
