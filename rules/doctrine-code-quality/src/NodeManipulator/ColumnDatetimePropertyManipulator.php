<?php

declare(strict_types=1);

namespace Rector\DoctrineCodeQuality\NodeManipulator;

use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\ColumnTagValueNode;

final class ColumnDatetimePropertyManipulator
{
    public function removeDefaultOption(ColumnTagValueNode $columnTagValueNode): void
    {
        $options = $columnTagValueNode->getOptions();
        unset($options['default']);

        $columnTagValueNode->changeItem('options', $options);
    }
}
