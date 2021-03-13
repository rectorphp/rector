<?php

namespace Rector\Tests\Naming\Rector\Variable\UnderscoreToCamelCaseLocalVariableNameRector\Fixture;

final class SkipUsedInNextOrPrevAssignVar
{
    public function foo()
    {
       $foo_bar = 4;
       $fooBar = 2;

       $testExample = 5;
       $test_example = 7;
    }
}
