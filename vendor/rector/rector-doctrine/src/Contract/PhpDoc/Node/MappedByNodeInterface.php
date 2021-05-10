<?php

declare (strict_types=1);
namespace Rector\Doctrine\Contract\PhpDoc\Node;

interface MappedByNodeInterface
{
    public function getMappedBy() : ?string;
    public function removeMappedBy() : void;
}
