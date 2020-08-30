<?php

declare(strict_types=1);

namespace Rector\Order\Contract;

interface RankeableInterface
{
    public function getName(): string;

    public function getRanks(): array;
}
