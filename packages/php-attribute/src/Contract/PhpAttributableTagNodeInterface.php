<?php

declare(strict_types=1);

namespace Rector\PhpAttribute\Contract;

interface PhpAttributableTagNodeInterface
{
    public function getShortName(): string;

    /**
     * @return mixed[]
     */
    public function getAttributableItems(): array;
}
