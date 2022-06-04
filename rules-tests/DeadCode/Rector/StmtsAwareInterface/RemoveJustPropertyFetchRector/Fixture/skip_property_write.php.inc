<?php

namespace Rector\Tests\DeadCode\Rector\StmtsAwareInterface\RemoveJustPropertyFetchRector\Fixture;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;

final class SkipPropertyWrite
{
    public function run(PhpDocNode $phpDocNode)
    {
        $phpDocChildNodes = $phpDocNode->children;
        $phpDocChildNode = array_pop($phpDocChildNodes);
    }
}
