<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Contract\PhpDocNode;

interface TagAwareNodeInterface
{
    public function getTag(): ?string;
}
