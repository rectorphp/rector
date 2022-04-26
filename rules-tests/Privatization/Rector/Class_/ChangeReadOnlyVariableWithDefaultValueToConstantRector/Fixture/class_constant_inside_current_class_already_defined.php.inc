<?php

namespace Rector\Tests\Privatization\Rector\Class_\ChangeReadOnlyVariableWithDefaultValueToConstantRector\Fixture;

use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\While_;

class ClassConstantInsideCurrentClassAlreadyDefined
{
    private const LOOP_NODES = [
        Do_::class,
        For_::class,
        While_::class,
    ];

    public function run()
    {
        $loopNodes = self::LOOP_NODES;
        foreach ($loopNodes as $loopNode) {

        }
    }
}

?>
-----
<?php

namespace Rector\Tests\Privatization\Rector\Class_\ChangeReadOnlyVariableWithDefaultValueToConstantRector\Fixture;

use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\While_;

class ClassConstantInsideCurrentClassAlreadyDefined
{
    private const LOOP_NODES = [
        Do_::class,
        For_::class,
        While_::class,
    ];

    public function run()
    {
        foreach (self::LOOP_NODES as $loopNode) {

        }
    }
}

?>
