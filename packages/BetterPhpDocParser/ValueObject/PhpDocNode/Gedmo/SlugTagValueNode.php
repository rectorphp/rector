<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Gedmo;

use Rector\BetterPhpDocParser\Contract\PhpDocNode\ShortNameAwareTagInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\SilentKeyNodeInterface;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\AbstractTagValueNode;

final class SlugTagValueNode extends AbstractTagValueNode implements ShortNameAwareTagInterface, SilentKeyNodeInterface
{
    /**
     * @return mixed[]
     */
    public function getFields(): array
    {
        return $this->items['fields'];
    }

    public function getShortName(): string
    {
        return '@Gedmo\Slug';
    }

    public function getSilentKey(): string
    {
        return 'fields';
    }
}
