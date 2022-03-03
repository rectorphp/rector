<?php

namespace Rector\Tests\Privatization\Rector\Class_\ChangeReadOnlyVariableWithDefaultValueToConstantRector\Fixture;

use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\While_;

class Bar
{
    protected const LOOP_NODES = [
        Do_::class,
        For_::class,
        While_::class,
    ];
}

class ClassConstantInsideCurrentClassAlreadyDefined extends Bar
{
    public function run()
    {
        $loopNodes = parent::LOOP_NODES;
        foreach ($loopNodes as $loopNode) {
            echo $loopNode;
        }
    }
}

(new ClassConstantInsideCurrentClassAlreadyDefined())->run();