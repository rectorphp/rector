<?php

namespace Rector\Tests\Php56\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector\Fixture;

class SkipMultipleCatchWithUseSameVariable
{
    public function run()
    {
        try {
        } catch (\Exception $e) {
            var_dump($e);
        } catch (\Throwable $e) {
            var_dump($e);
        }
    }

    public function run2()
    {
        try {
        } catch (\Exception $e) {
            $e;
        } catch (\Throwable $e) {
            $e;
        }
    }

    public function run3()
    {
        try {
        } catch (\Exception $e) {
            echo $e::class;
        } catch (\Throwable $e) {
            echo $e::class;
        }
    }
}
