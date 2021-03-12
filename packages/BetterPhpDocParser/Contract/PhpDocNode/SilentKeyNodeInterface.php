<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Contract\PhpDocNode;

interface SilentKeyNodeInterface
{
    public function getSilentKey(): string;
}
