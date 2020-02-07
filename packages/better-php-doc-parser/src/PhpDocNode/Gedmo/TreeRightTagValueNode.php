<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Gedmo;

use Gedmo\Mapping\Annotation\TreeRight;
use Rector\BetterPhpDocParser\PhpDocNode\AbstractTagValueNode;

final class TreeRightTagValueNode extends AbstractTagValueNode
{
    /**
     * @var string
     */
    public const CLASS_NAME = TreeRight::class;

    public function __toString(): string
    {
        return '';
    }
}
