<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject;

use Rector\Core\Exception\ShouldNotHappenException;

final class StartAndEnd
{
    public function __construct(
        private int $start,
        private int $end
    ) {
        if ($end < $start) {
            throw new ShouldNotHappenException();
        }
    }

    public function getStart(): int
    {
        return $this->start;
    }

    public function getEnd(): int
    {
        return $this->end;
    }

    public function contains(int $position): bool
    {
        if ($position < $this->start) {
            return false;
        }

        return $position < $this->end;
    }
}
