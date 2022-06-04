<?php

namespace Rector\Tests\DeadCode\Rector\StmtsAwareInterface\RemoveJustPropertyFetchRector\Fixture;

use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;

final class SkipDoublePropertyFetch
{
    public function run(PhpDocTagNode $phpDocTagNode)
    {
        if (! $phpDocTagNode->value instanceof GenericTagValueNode) {
            return null;
        }

        $description = $phpDocTagNode->value->value;
        if ($description === '') {
            return 'no';
        }

        return $description;
    }
}
