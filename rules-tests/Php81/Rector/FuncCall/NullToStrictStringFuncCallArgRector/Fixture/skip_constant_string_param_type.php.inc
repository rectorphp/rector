<?php

namespace Rector\Tests\Php80\Rector\FuncCall\NullToStrictStringFuncCallArgRector\Fixture;

final class SkipConstantStringParamType
{
    public function run($subject)
    {
        $subject = 'test';
        preg_split('#a#', $subject);
    }
}
