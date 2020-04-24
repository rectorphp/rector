<?php

declare(strict_types=1);

namespace Rector\PhpAttribute\Contract;

interface PhpAttributableTagNodeInterface
{
    public function toAttributeString(): string;

    public function printPhpAttributeItems(array $items): string;
}
