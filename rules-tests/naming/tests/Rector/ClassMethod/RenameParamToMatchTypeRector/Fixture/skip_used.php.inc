<?php

namespace Rector\Naming\Tests\Rector\ClassMethod\RenameParamToMatchTypeRector\Fixture;

final class SkipUsed
{
    public function run(Node $nd)
    {
        $this->betterNodeFinder->find($nd->stmts, function (Node $node) use ($nd) {
              return $nd !== $node;
        });
    }
}