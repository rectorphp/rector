<?php

namespace Rector\CodeQuality\Tests\Rector\Concat\JoinStringConcatRector\Fixture;

class SkipLongerThan120
{
    public function run()
    {
        $name =  'Adds $uuid property to entities, that already have $id with integer type.' .
            'Require for step-by-step migration from int to uuid. ' .
            'In following step it should be renamed to $id and replace it';
    }
}
