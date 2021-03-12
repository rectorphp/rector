<?php

namespace Rector\Tests\Php70\Rector\StaticCall\StaticCallOnNonStaticToInstanceCallRector\Fixture;

use Rector\Tests\Php70\Rector\StaticCall\StaticCallOnNonStaticToInstanceCallRector\Source\WithOnlyStaticMethods;

class SkipWithOnlyStaticMethods
{
    public function run()
    {
        return [
            'bool' => WithOnlyStaticMethods::aBoolMethod(),
            'string' => WithOnlyStaticMethods::aStringMethod()
        ];
    }
}
