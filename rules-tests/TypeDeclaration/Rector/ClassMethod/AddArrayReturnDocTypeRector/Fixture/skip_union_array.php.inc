<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\TemplateTagValueNode;

class SkipUnionArray
{
    /**
     * @return PhpDocTagNode[]|TemplateTagValueNode[]
     */
    public function getTags(array $tags): array
    {
        /** @var PhpDocTagNode[]|TemplateTagValueNode[] $tags */
        return $tags;
    }
}
