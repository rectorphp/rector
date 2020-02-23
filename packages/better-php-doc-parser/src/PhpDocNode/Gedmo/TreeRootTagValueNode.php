<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Gedmo;

use Rector\BetterPhpDocParser\PhpDocNode\AbstractTagValueNode;

final class TreeRootTagValueNode extends AbstractTagValueNode
{
    public function __toString(): string
    {
        return '';
    }
}
