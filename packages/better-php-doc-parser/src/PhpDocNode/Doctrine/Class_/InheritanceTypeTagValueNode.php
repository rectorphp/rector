<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Class_;

use Rector\BetterPhpDocParser\Contract\PhpDocNode\SilentKeyNodeInterface;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;

final class InheritanceTypeTagValueNode extends AbstractDoctrineTagValueNode implements SilentKeyNodeInterface
{
    public function getShortName(): string
    {
        return '@ORM\InheritanceType';
    }

    public function getSilentKey(): string
    {
        return 'value';
    }
}
