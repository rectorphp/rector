<?php

namespace Rector\Tests\Php56\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector\Fixture;

class SkipEmptyCheck
{
    public function run($check)
    {
        if ($check) {
            $data = 'value';
        }

        return ! empty($data)
            ? $data
            : 'default';
    }

    public function run2($check)
    {
        if ($check) {
            $data = 'value';
        }

        return empty($data)
            ? 'default'
            : $data;
    }

    public function run3()
    {
        if (empty($data)) {
            $data[] = 'test';
        }

        return $data;
    }
}
