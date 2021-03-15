<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Class_;

use Rector\BetterPhpDocParser\Contract\PhpDocNode\SilentKeyNodeInterface;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;

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
