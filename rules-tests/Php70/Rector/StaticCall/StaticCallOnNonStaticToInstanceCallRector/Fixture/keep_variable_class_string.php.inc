<?php

namespace Rector\Tests\Php70\Rector\StaticCall\StaticCallOnNonStaticToInstanceCallRector\Fixture;

use Rector\Tests\Php70\Rector\StaticCall\StaticCallOnNonStaticToInstanceCallRector\Source\WithOnlyStaticMethods;

class KeepVariableClassString
{
    public function run()
    {
        $class = WithOnlyStaticMethods::class;

        $class::aBoolMethod();
    }
}
