<?php

namespace Rector\Naming\Tests\Rector\ClassMethod\RenameParamToMatchTypeRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Name;

class KeepDoubleTrouble
{
    public function isNameIdentical(Node $node, Node $originalNode): bool
    {
        if (! $originalNode instanceof Name) {
            return false;
        }

        return true;
    }
}
