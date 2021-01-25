<?php

namespace Rector\CodeQuality\Tests\Rector\FuncCall\SetTypeToCastRector\Fixture;

class SkipAssign
{
    public function run($foo)
    {
        $result = settype($foo, 'string');

        is_bool(settype($foo, 'string'));

        $result = [settype($foo, 'string')];

        $result = [0 => settype($foo, 'string')];

        settype($foo, settype($foo, 'string'));
    }
}
