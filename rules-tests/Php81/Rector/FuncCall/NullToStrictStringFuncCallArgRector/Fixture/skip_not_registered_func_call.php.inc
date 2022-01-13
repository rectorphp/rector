<?php

namespace Rector\Tests\Php80\Rector\FuncCall\NullToStrictStringFuncCallArgRector\Fixture;

final class SkipNotRegisteredFuncCall
{
    public function run()
    {
        get_headers('http://www.google.com', false, null);
    }
}
