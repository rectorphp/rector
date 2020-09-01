<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface;

class SkipUnionArray
{
    /**
     * @return PhpDocTagNode[]|AttributeAwareNodeInterface[]
     */
    public function getTags(array $tags): array
    {
        /** @var PhpDocTagNode[]|AttributeAwareNodeInterface[] $tags */
        return $tags;
    }
}
