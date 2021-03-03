<?php

namespace Rector\Naming\Tests\Rector\Variable\UnderscoreToCamelCaseLocalVariableNameRector\Fixture;

final class DoNotOverwriteExistingVariablesEver
{
    public function foo()
    {
       $foo_bar[] = 4;
        
       $fooBar = 2;

       $testExample = 5;
       
       $test_example[] = 7;
    }
}
