<?php

namespace Rector\Tests\Php56\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector\Fixture;

class SkipIssetCheck
{
    public function run($check)
    {
        if ($check) {
            $data = 'value';
        }

        return isset($data)
            ? $data
            : 'default';
    }

    public function run2()
    {
        if (!isset($data)) {
            $data[] = 'test';
        }

        return $data;
    }
}
