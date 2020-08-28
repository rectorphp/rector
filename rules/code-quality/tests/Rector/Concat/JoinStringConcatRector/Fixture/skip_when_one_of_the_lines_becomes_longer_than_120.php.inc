<?php

namespace Rector\CodeQuality\Tests\Rector\Concat\JoinStringConcatRector\Fixture;

class SkipWhenOneOfTheLinesBecomesLongerThan120
{
    public function run()
    {
        $name = 'How it works? ' .
            '1. It adds $uuid property to entities. ' .
            '2. It makes things better. ' .
            'And finally: ' .
            'Require for step-by-step migration from int to uuid. ' .
            'In following step it should be renamed to $id and replace it';
    }
}
