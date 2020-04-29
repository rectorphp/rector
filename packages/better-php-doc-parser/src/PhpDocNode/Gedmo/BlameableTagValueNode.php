<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Gedmo;

use Gedmo\Mapping\Annotation\Blameable;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\ShortNameAwareTagInterface;
use Rector\BetterPhpDocParser\PhpDocNode\AbstractTagValueNode;

final class BlameableTagValueNode extends AbstractTagValueNode implements ShortNameAwareTagInterface
{
    public function __construct(Blameable $blameable)
    {
        $this->items = get_object_vars($blameable);
    }

    public function getShortName(): string
    {
        return '@Gedmo\Blameable';
    }
}
