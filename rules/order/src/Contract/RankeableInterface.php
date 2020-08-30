<?php

declare(strict_types=1);

namespace Rector\Order\Contract;

interface RankeableInterface
{
    public function getRanks(): array;
}
