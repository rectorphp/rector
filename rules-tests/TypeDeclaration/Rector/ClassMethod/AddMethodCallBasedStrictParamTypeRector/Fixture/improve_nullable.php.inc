<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddMethodCallBasedStrictParamTypeRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Stmt\Nop;

final class DoubleCallArray
{
    public function getName(?Node $node)
    {
        return $node;
    }
}

final class CallGetName
{
    public function run(DoubleCallArray $improveNullable)
    {
        $nop = new Nop();
        $improveNullable->getName($nop);

        $array = new Array_();
        $improveNullable->getName($array);
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddMethodCallBasedStrictParamTypeRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Stmt\Nop;

final class DoubleCallArray
{
    public function getName(\PhpParser\Node $node)
    {
        return $node;
    }
}

final class CallGetName
{
    public function run(DoubleCallArray $improveNullable)
    {
        $nop = new Nop();
        $improveNullable->getName($nop);

        $array = new Array_();
        $improveNullable->getName($array);
    }
}

?>
