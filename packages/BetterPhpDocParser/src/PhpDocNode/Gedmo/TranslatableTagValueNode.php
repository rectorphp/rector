<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Gedmo;

use Gedmo\Mapping\Annotation\Translatable;
use Rector\BetterPhpDocParser\PhpDocNode\AbstractTagValueNode;

final class TranslatableTagValueNode extends AbstractTagValueNode
{
    /**
     * @var string
     */
    public const CLASS_NAME = Translatable::class;

    public function __toString(): string
    {
        return '';
    }
}
