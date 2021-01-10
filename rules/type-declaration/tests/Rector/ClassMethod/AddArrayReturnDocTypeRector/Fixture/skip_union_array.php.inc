<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\PhpdocParserPrinter\Contract\AttributeAwareInterface;

class SkipUnionArray
{
    /**
     * @return PhpDocTagNode[]|AttributeAwareInterface[]
     */
    public function getTags(array $tags): array
    {
        /** @var PhpDocTagNode[]|AttributeAwareInterface[] $tags */
        return $tags;
    }
}
