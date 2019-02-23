<?php

namespace Rector\Celebrity\Tests\Rector\FuncCall\SetTypeToCastRector\Fixture;

class Assin
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
