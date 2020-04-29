<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Gedmo;

use Gedmo\Mapping\Annotation\Tree;
use Rector\BetterPhpDocParser\PhpDocNode\AbstractTagValueNode;

final class TreeTagValueNode extends AbstractTagValueNode
{
    public function __construct(Tree $tree)
    {
        $this->items = get_object_vars($tree);
    }
}
