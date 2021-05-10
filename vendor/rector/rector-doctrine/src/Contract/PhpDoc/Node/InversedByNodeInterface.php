<?php

declare (strict_types=1);
namespace Rector\Doctrine\Contract\PhpDoc\Node;

interface InversedByNodeInterface
{
    public function getInversedBy() : ?string;
    public function removeInversedBy() : void;
}
