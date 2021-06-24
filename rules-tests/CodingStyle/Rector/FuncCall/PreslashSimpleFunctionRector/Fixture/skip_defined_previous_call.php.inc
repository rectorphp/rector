<?php

namespace Rector\Tests\CodingStyle\Rector\FuncCall\PreslashSimpleFunctionRector\Fixture;

class SkipDefinedPreviousCall
{
    public function run()
    {
        function func_in_func($input) {
            echo $input;
        }

        func_in_func("Test");
    }
}

?>
