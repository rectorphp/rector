<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddParamTypeFromCallersRector\Fixture;

use PhpParser\Node\Stmt\Return_;

final class SomeClass
{
    public function run(Return_ $return)
    {
        $this->print($return);
    }

    public function print($return)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddParamTypeFromCallersRector\Fixture;

use PhpParser\Node\Stmt\Return_;

final class SomeClass
{
    public function run(Return_ $return)
    {
        $this->print($return);
    }

    public function print(\PhpParser\Node\Stmt\Return_ $return)
    {
    }
}

?>
