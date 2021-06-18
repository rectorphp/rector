<?php

namespace Rector\Tests\Php71\Rector\FuncCall\RemoveExtraParametersRector\Fixture;

if (! function_exists('\Rector\Tests\Php71\Rector\FuncCall\RemoveExtraParametersRector\Fixture\func_call_has_variadic_parameters')) {
    function func_call_has_variadic_parameters(): array
    {
        return func_get_args();
    }
}

final class SkipFuncGetAllOnFuncCall
{
    public function run()
    {
        return func_call_has_variadic_parameters(1);
    }
}
?>
