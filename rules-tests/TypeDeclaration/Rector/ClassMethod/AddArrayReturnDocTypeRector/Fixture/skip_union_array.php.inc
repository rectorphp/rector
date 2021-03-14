<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use PHPStan\PhpDocParser\Ast\BaseNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;


class SkipUnionArray
{
    /**
     * @return PhpDocTagNode[]|BaseNode[]
     */
    public function getTags(array $tags): array
    {
        /** @var PhpDocTagNode[]|BaseNode[] $tags */
        return $tags;
    }
}
