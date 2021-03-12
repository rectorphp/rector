<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddMethodCallBasedStrictParamTypeRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Stmt\Nop;

final class ImproveNullable
{
    public function getName(?Node $node)
    {
        return $node;
    }
}

final class CallGetName
{
    public function run(ImproveNullable $improveNullable)
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

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddMethodCallBasedStrictParamTypeRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Stmt\Nop;

final class ImproveNullable
{
    public function getName(\PhpParser\Node $node)
    {
        return $node;
    }
}

final class CallGetName
{
    public function run(ImproveNullable $improveNullable)
    {
        $nop = new Nop();
        $improveNullable->getName($nop);

        $array = new Array_();
        $improveNullable->getName($array);
    }
}

?>
