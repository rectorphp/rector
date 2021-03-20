<?php

declare(strict_types=1);

namespace Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

use PHPStan\PhpDocParser\Ast\Node;

final class SkipDuplicatedLast
{
    /**
     * @var \PhpParser\Node
     */
    private $node;

    public function findAllByType(array $foundTagsValueNodes): array
    {
        /** @var Node[] $foundTagsValueNodes */
        return $foundTagsValueNodes;
    }
}
