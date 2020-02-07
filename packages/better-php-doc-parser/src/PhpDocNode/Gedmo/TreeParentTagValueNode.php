<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Gedmo;

use Gedmo\Mapping\Annotation\TreeParent;
use Rector\BetterPhpDocParser\PhpDocNode\AbstractTagValueNode;

final class TreeParentTagValueNode extends AbstractTagValueNode
{
    /**
     * @var string
     */
    public const CLASS_NAME = TreeParent::class;

    public function __toString(): string
    {
        return '';
    }
}
