<?php

namespace Rector\Php70\Tests\Rector\StaticCall\StaticCallOnNonStaticToInstanceCallRector\Fixture;

use Rector\Php70\Tests\Rector\StaticCall\StaticCallOnNonStaticToInstanceCallRector\Source\WithOnlyStaticMethods;

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
