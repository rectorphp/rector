<?php declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Contract\Doctrine;

interface OriginalTagAwareInterface
{
    public function getOriginalTag(): ?string;
}
