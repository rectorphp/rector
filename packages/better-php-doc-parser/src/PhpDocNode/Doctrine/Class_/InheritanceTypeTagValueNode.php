<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Class_;

use Doctrine\ORM\Mapping\InheritanceType;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;

final class InheritanceTypeTagValueNode extends AbstractDoctrineTagValueNode
{
    public function __construct(InheritanceType $inheritanceType, ?string $originalContent)
    {
        $this->items = get_object_vars($inheritanceType);

        $this->resolveOriginalContentSpacingAndOrder($originalContent, 'value');
    }

    public function getShortName(): string
    {
        return '@ORM\InheritanceType';
    }
}
