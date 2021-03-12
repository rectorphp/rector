<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Gedmo;

use Rector\BetterPhpDocParser\Contract\PhpDocNode\ShortNameAwareTagInterface;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\AbstractTagValueNode;

final class SoftDeleteableTagValueNode extends AbstractTagValueNode implements ShortNameAwareTagInterface
{
    public function getFieldName(): string
    {
        return $this->items['fieldName'];
    }

    public function getShortName(): string
    {
        return '@Gedmo\SoftDeleteable';
    }
}
