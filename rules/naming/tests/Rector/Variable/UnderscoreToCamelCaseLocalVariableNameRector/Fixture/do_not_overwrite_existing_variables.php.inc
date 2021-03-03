<?php

namespace Rector\Naming\Tests\Rector\Variable\UnderscoreToCamelCaseLocalVariableNameRector\Fixture;

final class DoNotOverwriteExistingVariables
{
    public function foo()
    {
       foreach ([[4]] as list ($foo_bar)) {}

       foreach ([4] as $foo_bar) {}
        
       yield function () use (&$foo_bar) {
           list ($foo_bar) = [$foo_bar];
           return $foo_bar === 1;
       };

       $fooBar = 2;
       
       if ($foo_bar === 4) {
           $test_example = 1;

           $testExample = 5;

           yield function () use (&$test_example) {
               return $test_example === 1;
           };
       }
        

    }
}
?>
-----
