<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;

class TrueUnionType
{
    public function returnArray(ClassLike $classLike): array
    {
        if ($classLike instanceof Node\Stmt\Interface_) {
            return ['string1', 'string2', 'string3'];
        } elseif ($classLike instanceof Node\Stmt\Class_) {
            return [1, 2, 3];
        } else {
            return [true, false, true];
        }
    }
}
?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;

class TrueUnionType
{
    /**
     * @return string[]|int[]|bool[]
     */
    public function returnArray(ClassLike $classLike): array
    {
        if ($classLike instanceof Node\Stmt\Interface_) {
            return ['string1', 'string2', 'string3'];
        } elseif ($classLike instanceof Node\Stmt\Class_) {
            return [1, 2, 3];
        } else {
            return [true, false, true];
        }
    }
}
?>
