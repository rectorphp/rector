<?php

namespace Rector\Privatization\Tests\Rector\Class_\RepeatedLiteralToClassConstantRector\Fixture;

class SkipKeywords
{
    public function run()
    {
        $value = 'class';

        $value2 = 'class';

        $value3 = 'class';
    }
}
