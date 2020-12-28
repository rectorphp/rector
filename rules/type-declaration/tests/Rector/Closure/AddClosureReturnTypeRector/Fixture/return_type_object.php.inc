<?php

namespace Rector\TypeDeclaration\Tests\Rector\Closure\AddClosureReturnTypeRector\Fixture;

use PhpParser\Node\Stmt\Class_;

class ReturnTypeObject
{
    public function shouldSkip()
    {
        $nonAnonymousClassNodes = array_filter([], function (Class_ $classNode) {
            return $classNode->name;
        });
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\Closure\AddClosureReturnTypeRector\Fixture;

use PhpParser\Node\Stmt\Class_;

class ReturnTypeObject
{
    public function shouldSkip()
    {
        $nonAnonymousClassNodes = array_filter([], function (Class_ $classNode): ?\PhpParser\Node\Identifier {
            return $classNode->name;
        });
    }
}

?>
