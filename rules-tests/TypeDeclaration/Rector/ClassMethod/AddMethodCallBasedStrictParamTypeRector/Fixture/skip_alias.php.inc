<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddMethodCallBasedStrictParamTypeRector\Fixture;

use PhpParser\Node as PhpParserNode;

final class SkipAlias
{
    public function run(PhpParserNode $node)
    {
        $this->getById($node);
    }

    private function getById(PhpParserNode $node)
    {
    }
}
