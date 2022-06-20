<?php

namespace Rector\Tests\Php56\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector\Fixture;

class SkipCatchUseVariable
{
    public function run()
    {
        try {
        } catch (\Exception $e) {
            var_dump($e);
        }
    }

    public function run2()
    {
        try {
        } catch (\Exception $e) {
            $e;
        }
    }

    public function run3()
    {
        try {
        } catch (\Exception $e) {
            echo $e::class;
        }
    }
}
