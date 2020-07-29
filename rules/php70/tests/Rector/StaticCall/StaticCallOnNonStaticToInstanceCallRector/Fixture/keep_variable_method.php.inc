<?php

namespace Rector\Php70\Tests\Rector\StaticCall\StaticCallOnNonStaticToInstanceCallRector\Fixture;

use Rector\Php70\Tests\Rector\StaticCall\StaticCallOnNonStaticToInstanceCallRector\Source\WithOnlyStaticMethods;

class KeepVariableMethod
{
    public function run()
    {
        $method = 'aBoolMethod';

        WithOnlyStaticMethods::$method();
    }
}
