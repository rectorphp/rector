<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Gedmo;

use Gedmo\Mapping\Annotation\Versioned;
use Rector\BetterPhpDocParser\PhpDocNode\AbstractTagValueNode;

final class VersionedTagValueNode extends AbstractTagValueNode
{
    /**
     * @var string
     */
    public const SHORT_NAME = '@Gedmo\Versioned';

    /**
     * @var string
     */
    public const CLASS_NAME = Versioned::class;

    public function __toString(): string
    {
        return '';
    }
}
