<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Gedmo;

use Gedmo\Mapping\Annotation\TreeLevel;
use Rector\BetterPhpDocParser\PhpDocNode\AbstractTagValueNode;

final class TreeLevelTagValueNode extends AbstractTagValueNode
{
    /**
     * @var string
     */
    public const CLASS_NAME = TreeLevel::class;

    public function __toString(): string
    {
        return '';
    }
}
