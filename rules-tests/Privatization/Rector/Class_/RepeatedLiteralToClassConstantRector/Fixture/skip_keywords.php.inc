<?php

namespace Rector\Tests\Privatization\Rector\Class_\RepeatedLiteralToClassConstantRector\Fixture;

class SkipKeywords
{
    public function run()
    {
        $value = 'class';

        $value2 = 'class';

        $value3 = 'class';
    }
}
