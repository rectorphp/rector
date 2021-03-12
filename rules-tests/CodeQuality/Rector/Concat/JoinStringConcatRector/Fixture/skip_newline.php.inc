<?php

namespace Rector\Tests\CodeQuality\Rector\Concat\JoinStringConcatRector\Fixture;

class SkipNewline
{
    public function run()
    {
        return 'Hi' . "\n";
    }
}
