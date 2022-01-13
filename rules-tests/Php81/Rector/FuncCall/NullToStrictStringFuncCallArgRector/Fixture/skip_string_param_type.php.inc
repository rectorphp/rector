<?php

namespace Rector\Tests\Php80\Rector\FuncCall\NullToStrictStringFuncCallArgRector\Fixture;

final class SkipStringParamType
{
    /**
     * @param string $subject
     */
    public function run($subject)
    {
        preg_split('#a#', $subject);
    }
}
