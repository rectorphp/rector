<?php

namespace Rector\Tests\Php80\Rector\FuncCall\NullToStrictStringFuncCallArgRector\Fixture;

final class SkipNotRegisteredFuncCallWithNamedArgument
{
    public function run()
    {
        get_headers(string: 'http://www.google.com', associative: false, context: null);
    }
}
