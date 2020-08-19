<?php

namespace Rector\Php70\Tests\Rector\StaticCall\StaticCallOnNonStaticToInstanceCallRector\Fixture;

use Rector\Php70\Tests\Rector\StaticCall\StaticCallOnNonStaticToInstanceCallRector\Source\WithOnlyStaticMethods;

class KeepVariableClassString
{
    public function run()
    {
        $class = WithOnlyStaticMethods::class;

        $class::aBoolMethod();
    }
}
