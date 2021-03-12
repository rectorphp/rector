<?php

namespace Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

final class SkipBeforeTraverse extends NodeVisitorAbstract
{
    /**
     * @return Node[]|null
     */
    public function beforeTraverse(array $nodes): ?array
    {
        return parent::beforeTraverse($nodes);
    }
}
