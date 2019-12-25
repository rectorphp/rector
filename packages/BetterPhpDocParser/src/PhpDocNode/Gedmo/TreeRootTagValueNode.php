<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Gedmo;

use Gedmo\Mapping\Annotation\TreeRoot;
use Rector\BetterPhpDocParser\PhpDocNode\AbstractTagValueNode;

final class TreeRootTagValueNode extends AbstractTagValueNode
{
    /**
     * @var string
     */
    public const CLASS_NAME = TreeRoot::class;

    public function __toString(): string
    {
        return '';
    }
}
