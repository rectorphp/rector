<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Contract\Doctrine;

interface MappedByNodeInterface
{
    public function getMappedBy(): ?string;

    public function removeMappedBy(): void;
}
