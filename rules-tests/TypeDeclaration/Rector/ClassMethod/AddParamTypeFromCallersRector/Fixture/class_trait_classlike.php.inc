<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddParamTypeFromCallersRector\Fixture;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Trait_;

final class ClassTraitClassLike
{
    /**
     * @param Class_|Trait_ $class
     */
    public function run(\PhpParser\Node $class)
    {
        $this->process($class);
    }

    public function process($class)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddParamTypeFromCallersRector\Fixture;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Trait_;

final class ClassTraitClassLike
{
    /**
     * @param Class_|Trait_ $class
     */
    public function run(\PhpParser\Node $class)
    {
        $this->process($class);
    }

    public function process(\PhpParser\Node\Stmt\ClassLike $class)
    {
    }
}

?>
