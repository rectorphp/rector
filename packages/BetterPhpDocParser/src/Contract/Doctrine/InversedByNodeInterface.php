<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Contract\Doctrine;

interface InversedByNodeInterface
{
    public function getInversedBy(): ?string;

    public function removeInversedBy(): void;
}
