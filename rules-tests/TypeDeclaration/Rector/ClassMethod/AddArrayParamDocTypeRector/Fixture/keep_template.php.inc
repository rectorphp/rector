<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayParamDocTypeRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Scalar\String_;

final class KeepTemplate
{
    public function run(\PhpParser\Node $node)
    {
        return $this->findFirstParentByTypes([String_::class]);
    }

    /**
     * @template T of Node
     * @param class-string<T>[] $nodeTypes
     * @return T|null
     */
    public function findFirstParentByTypes(array $nodeTypes): ?Node
    {
        return $nodeTypes[0];
    }
}
