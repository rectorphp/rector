<?php

namespace Rector\Tests\Php70\Rector\StaticCall\StaticCallOnNonStaticToInstanceCallRector\Fixture;

use Rector\Tests\Php70\Rector\StaticCall\StaticCallOnNonStaticToInstanceCallRector\Source\WithOnlyStaticMethods;

class KeepVariableMethod
{
    public function run()
    {
        $method = 'aBoolMethod';

        WithOnlyStaticMethods::$method();
    }
}
