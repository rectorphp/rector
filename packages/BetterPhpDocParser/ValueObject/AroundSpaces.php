<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject;

final class AroundSpaces
{
    public function __construct(
        private string $closingSpace,
        private string $openingSpace
    ) {
    }

    public function getClosingSpace(): string
    {
        return $this->closingSpace;
    }

    public function getOpeningSpace(): string
    {
        return $this->openingSpace;
    }
}
