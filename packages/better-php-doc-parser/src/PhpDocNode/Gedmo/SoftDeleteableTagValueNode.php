<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Gedmo;

use Gedmo\Mapping\Annotation\SoftDeleteable;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\ShortNameAwareTagInterface;
use Rector\BetterPhpDocParser\PhpDocNode\AbstractTagValueNode;

final class SoftDeleteableTagValueNode extends AbstractTagValueNode implements ShortNameAwareTagInterface
{
    public function __construct(SoftDeleteable $softDeleteable)
    {
        $this->items = get_object_vars($softDeleteable);
    }

    public function getFieldName(): string
    {
        return $this->items['fieldName'];
    }

    public function getShortName(): string
    {
        return '@Gedmo\SoftDeleteable';
    }
}
