<?php

namespace Rector\DeadCode\Tests\Rector\If_\RemoveDeadInstanceOfRector\Fixture;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Interface_;

class SkipUsePhpdocInfo
{
    /**
     * @param Class_|Interface_ $classLike
     */
    public function getClassLikeNodeParentInterfaceNames(ClassLike $classLike): array
    {
        if ($classLike instanceof Class_) {
            return false;
        }
        return true;
    }
}

?>
