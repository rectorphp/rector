<?php

namespace Rector\Tests\TypeDeclaration\Rector\Closure\AddClosureReturnTypeRector\Fixture;

use PhpParser\Node\Stmt\Class_;

class ReturnTypeObject
{
    public function shouldSkip(Class_ $class)
    {
        $nonAnonymousClassNodes = array_filter([$class], function (Class_ $classNode) {
            return $classNode->name;
        });
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\Closure\AddClosureReturnTypeRector\Fixture;

use PhpParser\Node\Stmt\Class_;

class ReturnTypeObject
{
    public function shouldSkip(Class_ $class)
    {
        $nonAnonymousClassNodes = array_filter([$class], function (Class_ $classNode): ?\PhpParser\Node\Identifier {
            return $classNode->name;
        });
    }
}

?>
