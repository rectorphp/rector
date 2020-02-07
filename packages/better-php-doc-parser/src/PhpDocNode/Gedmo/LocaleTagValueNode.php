<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Gedmo;

use Gedmo\Mapping\Annotation\Locale;
use Rector\BetterPhpDocParser\PhpDocNode\AbstractTagValueNode;

final class LocaleTagValueNode extends AbstractTagValueNode
{
    /**
     * @var string
     */
    public const CLASS_NAME = Locale::class;

    public function __toString(): string
    {
        return '';
    }
}
