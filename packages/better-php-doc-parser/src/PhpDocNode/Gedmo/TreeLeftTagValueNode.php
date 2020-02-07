<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Gedmo;

use Gedmo\Mapping\Annotation\TreeLeft;
use Rector\BetterPhpDocParser\PhpDocNode\AbstractTagValueNode;

final class TreeLeftTagValueNode extends AbstractTagValueNode
{
    /**
     * @var string
     */
    public const CLASS_NAME = TreeLeft::class;

    public function __toString(): string
    {
        return '';
    }
}
