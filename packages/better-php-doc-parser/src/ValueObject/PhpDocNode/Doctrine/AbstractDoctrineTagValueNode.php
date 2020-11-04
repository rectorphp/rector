<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine;

use Rector\BetterPhpDocParser\Contract\Doctrine\DoctrineTagNodeInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\ShortNameAwareTagInterface;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\AbstractTagValueNode;

abstract class AbstractDoctrineTagValueNode extends AbstractTagValueNode implements DoctrineTagNodeInterface, ShortNameAwareTagInterface
{
    /**
     * @return mixed[]
     */
    public function getAttributableItems(): array
    {
        return $this->filterOutMissingItems($this->items);
    }
}
