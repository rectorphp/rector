<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;

class TrueValueUnionType
{
    public function returnArray(ClassLike $classLike): array
    {
        if ($classLike instanceof Node\Stmt\Interface_) {
            return ['string1', 1, true];
        } elseif ($classLike instanceof Node\Stmt\Class_) {
            return ['string2', 3, false];
        } else {
            return ['string3', 2, true];
        }
    }
}
?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;

class TrueValueUnionType
{
    /**
     * @return array<int|string|bool>
     */
    public function returnArray(ClassLike $classLike): array
    {
        if ($classLike instanceof Node\Stmt\Interface_) {
            return ['string1', 1, true];
        } elseif ($classLike instanceof Node\Stmt\Class_) {
            return ['string2', 3, false];
        } else {
            return ['string3', 2, true];
        }
    }
}
?>
